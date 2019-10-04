<?php

namespace Tmv\WhatsApi\Message\Action;

interface MessageInterface extends ActionInterface
{

    /**
     * @return string
     */
    public function getFromName();

    /**
     * @return string
     */
    public function getTo();

    /**
     * @return int
     */
    public function getTimestamp();

    /**
     * @param int $timestamp
     * @return $this
     */
    public function setTimestamp($timestamp);

    /**
     * @return \Tmv\WhatsApi\Message\Node\NodeInterface
     */
    public function getSubNode();
}
 