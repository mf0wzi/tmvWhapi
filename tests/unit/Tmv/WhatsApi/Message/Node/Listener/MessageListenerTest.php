<?php

namespace Tmv\WhatsApi\Message\Node\Listener;

use \Mockery as m;
use Tmv\WhatsApi\Client\Client;

class MessageListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MessageListener
     */
    protected $object;

    public function setUp()
    {
        $this->object = new MessageListener();
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
        $node = m::mock('\Tmv\WhatsApi\Message\Node\Message');
        $eventManagerMock = m::mock('\Zend\EventManager\EventManagerInterface');
        $client = m::mock('\Tmv\WhatsApi\Client\Client');
        $phoneMock = m::mock('\Tmv\WhatsApi\Entity\Phone');
        $messageQueueMock = m::mock('\Tmv\WhatsApi\Message\MessageQueue');
        $nodeBody = m::mock('\Tmv\WhatsApi\Message\Node\NodeInterface');
        $parkedMock = m::mock('\Tmv\WhatsApi\Message\Action\MessageInterface');

        $event->shouldReceive('getNode')->once()->andReturn($node);
        $event->shouldReceive('getClient')->once()->andReturn($client);
        $client->shouldReceive('getEventManager')->times(3)->andReturn($eventManagerMock);
        $client->shouldReceive('getPhone')->once()->andReturn($phoneMock);
        $client->shouldReceive('getMessageQueue')->times(5)->andReturn($messageQueueMock);
        $client->shouldReceive('sendNextMessage')->once();
        $phoneMock->shouldReceive('getPhoneNumber')->once()->andReturn('0123456789');

        $parkedMock->shouldReceive('getId')->once()->andReturn('the id');

        $messageQueueMock->shouldReceive('hasParked')->once()->andReturn(true);
        $messageQueueMock->shouldReceive('getParked')->once()->andReturn($parkedMock);
        $messageQueueMock->shouldReceive('getParkedTime')->once()->andReturn(0);
        $messageQueueMock->shouldReceive('removeParked')->twice();

        $node->shouldReceive('getFrom')->times(3)->andReturn('somethingelse@s.us');
        $node->shouldReceive('hasChild')->with('request')->once()->andReturn(false);
        $node->shouldReceive('hasChild')->with('received')->once()->andReturn(false);
        $node->shouldReceive('hasChild')->with('x')->once()->andReturn(true);
        $node->shouldReceive('getChild')->with('body')->twice()->andReturn($nodeBody);
        $node->shouldReceive('getAttribute')->with('from')->once()->andReturn('somethingelse@s.us');
        $node->shouldReceive('getAttribute')->with('type')->once()->andReturn('the type');
        $node->shouldReceive('getAttribute')->with('id')->twice()->andReturn('the id');
        $node->shouldReceive('getAttribute')->with('t')->once()->andReturn(123);
        $nodeBody->shouldReceive('getData')->once()->andReturn('the body');
        //$client->shouldReceive('send')->once();


        $eventManagerMock->shouldReceive('trigger')->times(3);

        /*
        $eventManagerMock->shouldReceive('trigger')
            ->once()
            ->withArgs(
                array(
                    'message.not-received-server',
                    $client,
                    array(
                        $node
                    )
                )
            );
        $eventManagerMock->shouldReceive('trigger')
            ->once()
            ->withArgs(
                array(
                    'message.received-server',
                    $client,
                    $node
                )
            );
        */

        $this->object->onReceivedNode($event);
    }

    protected function tearDown()
    {
        m::close();
    }
}
