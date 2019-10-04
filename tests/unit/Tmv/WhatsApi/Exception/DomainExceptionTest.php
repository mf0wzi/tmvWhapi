<?php

namespace Tmv\WhatsApi\Exception;

class DomainExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DomainException
     */
    protected $object;

    public function setUp()
    {
        $this->object = new DomainException();
    }

    public function testInstance()
    {
        $this->assertInstanceOf('\Tmv\WhatsApi\Exception\DomainException', $this->object);
    }
}
