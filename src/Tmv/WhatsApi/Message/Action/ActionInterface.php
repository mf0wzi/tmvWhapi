<?php

namespace Tmv\WhatsApi\Message\Action;

interface ActionInterface
{
    /**
     * @param string $id
     * @return string
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getId();

    /**
     * @return \Tmv\WhatsApi\Message\Node\NodeInterface
     */
    public function getNode();

    /**
     * @param \Tmv\WhatsApi\Message\Node\NodeInterface $node
     * @return $this
     */
    public function setNode($node);

    /**
     * @return $this
     */
    public function buildNode();
}
