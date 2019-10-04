<?php

namespace Tmv\WhatsApi\Message\Event;

use Tmv\WhatsApi\Message\Node\NodeInterface;

class NodeEvent extends AbstractEvent
{
    /**
     * @var NodeInterface
     */
    protected $node;

    /**
     * @param  NodeInterface $node
     * @return $this
     */
    public function setNode(NodeInterface $node)
    {
        $this->node = $node;

        return $this;
    }

    /**
     * @return NodeInterface
     */
    public function getNode()
    {
        return $this->node;
    }
}
