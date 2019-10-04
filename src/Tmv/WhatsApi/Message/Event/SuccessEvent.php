<?php

namespace Tmv\WhatsApi\Message\Event;

use Tmv\WhatsApi\Exception\RuntimeException;

class SuccessEvent extends NodeEvent
{
    /**
     * @var string
     */
    protected $name = 'login.success';

    /**
     * @param $name
     * @return null
     * @throws \Tmv\WhatsApi\Exception\RuntimeException
     */
    public function setName($name)
    {
        throw new RuntimeException("Name for this event can't be changed");
    }
}
