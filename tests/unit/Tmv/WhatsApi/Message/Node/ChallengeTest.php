<?php

namespace Tmv\WhatsApi\Message\Node;

class ChallengeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Challenge
     */
    protected $object;

    public function setUp()
    {
        $this->object = new Challenge();
    }

    public function testGetNameMethod()
    {
        $this->assertEquals('challenge', $this->object->getName());
    }

    /**
     * @expectedException \Tmv\WhatsApi\Exception\InvalidArgumentException
     */
    public function testSetNameMethod()
    {
        $this->object->setName('foo');
    }
}
