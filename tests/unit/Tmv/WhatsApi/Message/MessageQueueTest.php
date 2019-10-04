<?php

namespace Tmv\WhatsApi\Message;

use \Mockery as m;

class MessageQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MessageQueue
     */
    protected $object;

    public function setUp()
    {
        $this->object = new MessageQueue();
    }

    /**
     * @expectedException \Tmv\WhatsApi\Exception\InvalidArgumentException
     */
    public function testEnqueueMethodException()
    {
        $this->object->enqueue(new \stdClass());
    }

    public function testQueueFlow()
    {
        $messageMock = m::mock('\Tmv\WhatsApi\Message\Action\MessageInterface');

        $this->assertFalse($this->object->hasParked());
        $this->assertNull($this->object->getParked());
        $this->assertEquals(0, $this->object->getParkedTime());
        $this->assertCount(0, $this->object);

        $this->object->addMessage($messageMock);

        $this->assertFalse($this->object->hasParked());
        $this->assertNull($this->object->getParked());
        $this->assertEquals(0, $this->object->getParkedTime());
        $this->assertCount(1, $this->object);

        $now = time();
        $message = $this->object->getNextMessage();

        $this->assertEquals($message, $messageMock);
        $this->assertTrue($this->object->hasParked());
        $this->assertEquals($message, $this->object->getParked());
        $this->assertGreaterThanOrEqual($now, $this->object->getParkedTime());
        $this->assertCount(0, $this->object);

        $this->object->removeParked();

        $this->assertFalse($this->object->hasParked());
        $this->assertNull($this->object->getParked());
        $this->assertEquals(0, $this->object->getParkedTime());
        $this->assertCount(0, $this->object);
    }

    protected function tearDown()
    {
        m::close();
    }
}
