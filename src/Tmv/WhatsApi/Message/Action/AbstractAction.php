<?php

namespace Tmv\WhatsApi\Message\Action;

use Tmv\WhatsApi\Message\Node\NodeFactory;
use Tmv\WhatsApi\Message\Node\NodeInterface;

abstract class AbstractAction implements ActionInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var NodeInterface
     */
    protected $node;

    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Tmv\WhatsApi\Message\Node\NodeInterface $node
     * @return $this
     */
    public function setNode($node)
    {
        $this->node = $node;
        return $this;
    }

    /**
     * @return \Tmv\WhatsApi\Message\Node\NodeInterface
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @param  NodeFactory $nodeFactory
     * @return $this
     */
    public function setNodeFactory(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;

        return $this;
    }

    /**
     * @return NodeFactory
     */
    public function getNodeFactory()
    {
        if (!$this->nodeFactory) {
            $this->nodeFactory = new NodeFactory();
        }

        return $this->nodeFactory;
    }
}
