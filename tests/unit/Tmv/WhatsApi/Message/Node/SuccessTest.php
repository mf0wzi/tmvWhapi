<?php

namespace Tmv\WhatsApi\Message\Node;

class SuccessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Success
     */
    protected $object;

    public function setUp()
    {
        $this->object = new Success();
    }

    public function testGetNameMethod()
    {
        $this->assertEquals('success', $this->object->getName());
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
        $this->object->setAttribute('t', 123);
        $this->assertEquals(123, $this->object->getTimestamp());

        $this->object->setAttribute('creation', 123);
        $this->assertEquals(123, $this->object->getCreation());

        $this->object->setAttribute('expiration', 123);
        $this->assertEquals(123, $this->object->getExpiration());

        $this->object->setAttribute('kind', 123);
        $this->assertEquals(123, $this->object->getKind());

        $this->object->setAttribute('status', 123);
        $this->assertEquals(123, $this->object->getStatus());
    }
}
