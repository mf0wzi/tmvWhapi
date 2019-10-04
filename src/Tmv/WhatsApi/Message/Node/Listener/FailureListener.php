<?php

namespace Tmv\WhatsApi\Message\Node\Listener;

use Tmv\WhatsApi\Message\Event\FailureEvent;
use Tmv\WhatsApi\Message\Event\ReceivedNodeEvent;
use Tmv\WhatsApi\Message\Node\NodeInterface;
use Zend\EventManager\EventManagerInterface;

class FailureListener extends AbstractListener
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
        $this->listeners[] = $events->attach('received.node.failure', array($this, 'onReceivedNode'));
    }

    public function onReceivedNode(ReceivedNodeEvent $e)
    {
        /** @var NodeInterface $node */
        $node = $e->getNode();
        $client = $e->getClient();

        // triggering public event
        $event = new FailureEvent();
        $event->setClient($client);
        $event->setTarget($client);
        $event->setNode($node);
        $client->getEventManager()->trigger($event);
    }
}
