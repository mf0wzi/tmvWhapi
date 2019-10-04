<?php

namespace Tmv\WhatsApi\Message\Node;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Message
     */
    protected $object;

    public function setUp()
    {
        $this->object = new Message();
    }

    public function testGetNameMethod()
    {
        $this->assertEquals('message', $this->object->getName());
    }

    /**
     * @expectedException \Tmv\WhatsApi\Exception\InvalidArgumentException
     */
    public function testSetNameMethod()
    {
        $this->object->setName('foo');
    }

    public function testSettersAndGetters()
    {
        $this->object->setId(123);
        $this->assertEquals(123, $this->object->getId());

        $this->object->setFrom(123);
        $this->assertEquals(123, $this->object->getFrom());
    }
}
