<?php

namespace Tmv\WhatsApi\Message\Node;

class IqTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Iq
     */
    protected $object;

    public function setUp()
    {
        $this->object = new Iq();
    }

    public function testGetNameMethod()
    {
        $this->assertEquals('iq', $this->object->getName());
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
        $this->object->setType('foo');
        $this->assertEquals('foo', $this->object->getType());
        $this->assertEquals('foo', $this->object->getAttribute('type'));

        $this->object->setTo('foo');
        $this->assertEquals('foo', $this->object->getTo());
        $this->assertEquals('foo', $this->object->getAttribute('to'));
    }
}
