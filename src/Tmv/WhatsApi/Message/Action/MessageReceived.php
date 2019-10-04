<?php

namespace Tmv\WhatsApi\Message\Action;

use Tmv\WhatsApi\Exception\InvalidArgumentException;
use Tmv\WhatsApi\Message\Node\Message;

/**
 * Class MessageReceived
 * Tell the server we received the message.
 *
 * @package Tmv\WhatsApi\Message\Action
 */
class MessageReceived extends AbstractAction
{

    const RESPONSE_RECEIVED = 'received';
    const RESPONSE_ACK = 'ack';

    /**
     * @var string
     */
    protected $to;
    /**
     * @var string
     */
    protected $response;

    /**
     * @param Message $message
     * @return MessageReceived
     * @throws \Tmv\WhatsApi\Exception\InvalidArgumentException
     */
    public static function fromMessageNode(Message $message)
    {
        $requestNode = $message->getChild("request");
        $receivedNode = $message->getChild("received");
        if (null !== $requestNode || null !== $receivedNode) {
            $response = "received";
            if (null !== $receivedNode) {
                $response = "ack";
            }

            return new static($message->getFrom(), $message->getId(), $response);
        }
        throw new InvalidArgumentException("This Message node can't be used to initialize this class");
    }

    public function __construct($to, $id, $response)
    {
        $this->setTo($to);
        $this->setId($id);
        $this->setResponse($response);
    }

    /**
     * @return $this
     */
    public function buildNode()
    {
        $node = $this->getNodeFactory()->fromArray(
            array(
                'name'       => 'message',
                'attributes' => array(
                    'to'   => $this->getTo(),
                    'type' => 'chat',
                    'id' => $this->getId(),
                    't' => time()
                ),
                'children'   => array(
                    array(
                        'name' => $this->getResponse(),
                        'attributes' => array(
                            'xmlns' => 'urn:xmpp:receipts'
                        )
                    )
                )
            )
        );

        $this->setNode($node);
        return $this;
    }

    /**
     * @param  string $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param  string $to
     * @return $this
     */
    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }
}
