<?php

namespace Tmv\WhatsApi\Exception;

class RuntimeExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RuntimeException
     */
    protected $object;

    public function setUp()
    {
        $this->object = new RuntimeException();
    }

    public function testInstance()
    {
        $this->assertInstanceOf('\Tmv\WhatsApi\Exception\RuntimeException', $this->object);
    }
}
