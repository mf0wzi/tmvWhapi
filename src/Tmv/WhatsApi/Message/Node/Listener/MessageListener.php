<?php

namespace Tmv\WhatsApi\Message\Node\Listener;

use Tmv\WhatsApi\Client\Client;
use Tmv\WhatsApi\Message\Action\MessageReceived;
use Tmv\WhatsApi\Message\Event\ReceivedNodeEvent;
use Tmv\WhatsApi\Message\Node\Message;
use Zend\EventManager\EventManagerInterface;

class MessageListener extends AbstractListener
{

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('received.node.message', array($this, 'onReceivedNode'));
    }

    public function onReceivedNode(ReceivedNodeEvent $e)
    {
        /** @var Message $node */
        $node = $e->getNode();
        $client = $e->getClient();

        // @todo: triggering public events

        //do not send received confirmation if sender is yourself
        $fromMeString = $client->getPhone()->getPhoneNumber() . '@' . Client::WHATSAPP_SERVER;
        if ($node->getFrom() && strpos($node->getFrom(), $fromMeString) === false
            && ($node->hasChild("request") || $node->hasChild("received"))
        ) {
            $action = MessageReceived::fromMessageNode($node);
            $client->send($action);
        }

        // check if it is a response to a status request
        $foo = explode('@', $node->getFrom());
        if (is_array($foo) && count($foo) > 1 && strcmp($foo[1], "s.us") == 0 && $node->getChild('body') != null) {
            $params = array(
                $node->getAttribute('from'),
                $node->getAttribute('type'),
                $node->getAttribute('id'),
                $node->getAttribute('t'),
                $node->getChild("body")->getData()
            );
            $client->getEventManager()->trigger('status.received', $client, $params);
        }

        // check for message received ack
        if ($node->hasChild('x')
            && $client->getMessageQueue()->hasParked()
            && $client->getMessageQueue()->getParked()->getId() == $node->getAttribute('id')
        ) {
            $client->getMessageQueue()->removeParked();
            $client->sendNextMessage();

            $client->getEventManager()->trigger('message.received-server', $client, array($node));
        }

        // check if sent message is expired, we don't wait anymore
        if ($client->getMessageQueue()->getParkedTime() < time() - 5) {
            $client->getMessageQueue()->removeParked();
            $client->getEventManager()->trigger('message.not-received-server', $client, array($node));
        }
    }
}
