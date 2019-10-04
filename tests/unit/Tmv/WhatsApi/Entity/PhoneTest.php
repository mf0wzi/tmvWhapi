<?php

namespace Tmv\WhatsApi\Entity;

class PhoneTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Phone
     */
    protected $object;

    public function setUp()
    {
        $this->object = new Phone('390123456789');
    }

    public function testSettersAndGettersMethods()
    {
        $this->assertEquals('390123456789', $this->object->getPhoneNumber());
        $this->object->setPhoneNumber('333');
        $this->assertEquals('333', $this->object->getPhoneNumber());

        $this->object->setPhone('phone');
        $this->assertEquals('phone', $this->object->getPhone());

        $this->object->setCc('other');
        $this->assertEquals('other', $this->object->getCc());

        $this->object->setCountry('other');
        $this->assertEquals('other', $this->object->getCountry());

        $this->object->setIso3166('other');
        $this->assertEquals('other', $this->object->getIso3166());

        $this->object->setIso639('other');
        $this->assertEquals('other', $this->object->getIso639());

        $this->object->setMcc('other');
        $this->assertEquals('other', $this->object->getMcc());
    }
}
