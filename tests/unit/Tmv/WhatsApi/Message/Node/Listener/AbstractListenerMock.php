<?php

namespace Tmv\WhatsApi\Message\Node\Listener;

use Zend\EventManager\EventManagerInterface;

class AbstractListenerMock extends AbstractListener
{

    /**
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('test.event');
    }
}
