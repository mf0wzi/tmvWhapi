<?php

namespace Tmv\WhatsApi\Message\Action;

/**
 * Class MessageText
 * Send a text message
 *
 * @package Tmv\WhatsApi\Message\Action
 */
class MessageText extends AbstractMessage
{

    /**
     * @var string
     */
    protected $body = '';

    /**
     * @return \Tmv\WhatsApi\Message\Node\NodeInterface
     */
    public function getSubNode()
    {
        $node = $this->getNodeFactory()->fromArray(
            array(
                'name' => 'body',
                'data' => $this->getBody()
            )
        );

        return $node;
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}
