<?php

namespace Tmv\WhatsApi\Message\Node;

use \Mockery as m;

class StreamFeaturesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StreamFeatures
     */
    protected $object;

    public function setUp()
    {
        $this->object = new StreamFeatures();
    }

    public function testGetNameMethod()
    {
        $this->assertEquals('stream:features', $this->object->getName());
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
        $nodeMock = m::mock('\Tmv\WhatsApi\Message\Node\Node');
        $nodeMock->shouldReceive('getName')->andReturn('w:profile:picture');
        $nodeFactoryMock = m::mock('\Tmv\WhatsApi\Message\Node\NodeFactory');
        $nodeFactoryMock->shouldReceive('fromArray')->once()->andReturn($nodeMock);

        $this->object->setNodeFactory($nodeFactoryMock);

        $this->assertFalse($this->object->hasProfileSubscribe(), "Should be empty");

        $this->object->addProfileSubscribe();
        $this->assertTrue($this->object->hasProfileSubscribe(), "Should be true");

        $this->object->removeProfileSubscribe();
        $this->assertFalse($this->object->hasProfileSubscribe(), "should be false");
    }

    protected function tearDown()
    {
        m::close();
    }
}
