<?php

namespace Tmv\WhatsApi\Exception;

class InvalidArgumentExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InvalidArgumentException
     */
    protected $object;

    public function setUp()
    {
        $this->object = new InvalidArgumentException();
    }

    public function testInstance()
    {
        $this->assertInstanceOf('\Tmv\WhatsApi\Exception\InvalidArgumentException', $this->object);
    }
}
