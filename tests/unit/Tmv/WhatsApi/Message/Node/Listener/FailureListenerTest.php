<?php

namespace Tmv\WhatsApi\Message\Node\Listener;

use \Mockery as m;

class FailureListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FailureListener
     */
    protected $object;

    public function setUp()
    {
        $this->object = new FailureListener();
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

    public function testOnReceivedNodeMethod()
    {
        $event = m::mock('\Tmv\WhatsApi\Message\Event\ReceivedNodeEvent');
        $node = m::mock('\Tmv\WhatsApi\Message\Node\NodeInterface');
        $eventManagerMock = m::mock('\Zend\EventManager\EventManagerInterface');
        $client = m::mock('\Tmv\WhatsApi\Client\Client');

        $event->shouldReceive('getNode')->once()->andReturn($node);
        $event->shouldReceive('getClient')->once()->andReturn($client);
        $client->shouldReceive('getEventManager')->once()->andReturn($eventManagerMock);
        $eventManagerMock->shouldReceive('trigger')->once();

        $this->object->onReceivedNode($event);
    }

    protected function tearDown()
    {
        m::close();
    }
}
