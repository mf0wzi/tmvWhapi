<?php

namespace Tmv\WhatsApi\Message\Node;

class NodeFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NodeFactory
     */
    protected $object;

    public function setUp()
    {
        $this->object = new NodeFactory();
    }

    public function testFromUnknownNode()
    {
        $this->assertInstanceOf(
            '\Tmv\WhatsApi\Message\Node\Node',
            $this->object->fromArray(array('name' => 'baz')),
            'Node instance'
        );
    }

    public function testFromChallengeNode()
    {
        $this->assertInstanceOf(
            '\Tmv\WhatsApi\Message\Node\Challenge',
            $this->object->fromArray(array('name' => 'challenge')),
            'Challenge instance'
        );
    }

    public function testFromIqNode()
    {
        $this->assertInstanceOf(
            '\Tmv\WhatsApi\Message\Node\Iq',
            $this->object->fromArray(array('name' => 'iq')),
            'Iq instance'
        );
    }

    public function testFromStreamFeaturesNode()
    {
        $this->assertInstanceOf(
            '\Tmv\WhatsApi\Message\Node\StreamFeatures',
            $this->object->fromArray(array('name' => 'stream:features')),
            'StreamFeatures instance'
        );
    }

    public function testFromSuccessNode()
    {
        $this->assertInstanceOf(
            '\Tmv\WhatsApi\Message\Node\Success',
            $this->object->fromArray(array('name' => 'success')),
            'Success instance'
        );
    }

    public function testFromMessageNode()
    {
        $this->assertInstanceOf(
            '\Tmv\WhatsApi\Message\Node\Message',
            $this->object->fromArray(array('name' => 'message')),
            'Message instance'
        );
    }

    /**
     * @expectedException \Tmv\WhatsApi\Exception\InvalidArgumentException
     */
    public function testWithoutNameException()
    {
        $this->object->fromArray(array());
    }
}
