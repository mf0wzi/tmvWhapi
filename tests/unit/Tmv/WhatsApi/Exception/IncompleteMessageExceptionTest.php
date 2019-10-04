<?php

namespace Tmv\WhatsApi\Exception;

class IncompleteMessageExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IncompleteMessageException
     */
    protected $object;

    public function setUp()
    {
        $this->object = new IncompleteMessageException();
    }

    public function testInstance()
    {
        $this->assertInstanceOf('\Tmv\WhatsApi\Exception\IncompleteMessageException', $this->object);
    }

    public function testSettersAndGettersMethods()
    {
        $this->assertNull($this->object->getInput());
        $this->object->setInput('input');
        $this->assertEquals('input', $this->object->getInput());
    }
}
