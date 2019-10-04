<?php

namespace Tmv\WhatsApi\Message\Node;

class MessageTextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MessageText
     */
    protected $object;

    public function setUp()
    {
        $this->object = new MessageText();
    }

    public function testSettersAndGetters()
    {
        $this->assertNull($this->object->getBody());
        $this->object->setBody(123);
        $this->assertEquals(123, $this->object->getBody());

        $this->assertFalse($this->object->hasNotify());
        $this->assertNull($this->object->getNotify());
    }
}
