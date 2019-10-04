<?php

namespace Tmv\WhatsApi\Message\Node\Listener;

use \Mockery as m;

class AbstractListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractListenerMock
     */
    protected $object;

    public function setUp()
    {
        $this->object = new AbstractListenerMock();
    }

    public function testAttachAndDetachMethod()
    {
        $this->assertCount(0, $this->object->getListeners());
        $eventManagerMock = m::mock('\Zend\EventManager\EventManagerInterface');
        $eventManagerMock->shouldReceive('attach')->once();
        $this->object->attach($eventManagerMock);
        $this->assertCount(1, $this->object->getListeners());

        $eventManagerMock->shouldReceive('detach')->once()->andReturn(true);
        $this->object->detach($eventManagerMock);
        $this->assertCount(0, $this->object->getListeners());
    }

    protected function tearDown()
    {
        m::close();
    }
}
