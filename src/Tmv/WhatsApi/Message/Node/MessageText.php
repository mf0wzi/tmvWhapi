<?php

namespace Tmv\WhatsApi\Message\Node;

class MessageText extends Message
{

    /**
     * @return null|string
     */
    public function getBody()
    {
        if ($this->hasChild('body')) {
            return $this->getChild('body')->getData();
        }
        return null;
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $nodeBody = $this->getChild('body');
        if (!$nodeBody) {
            $nodeBody = new Node();
            $nodeBody->setName('body');
            $this->addChild($nodeBody);
        }
        $nodeBody->setData($body);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasNotify()
    {
        return $this->hasChild('notify');
    }

    /**
     * @return NodeInterface
     */
    public function getNotify()
    {
        return $this->getChild('notify');
    }

}
