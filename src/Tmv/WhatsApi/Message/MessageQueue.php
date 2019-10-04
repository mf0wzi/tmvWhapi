<?php

namespace Tmv\WhatsApi\Message;

use Tmv\WhatsApi\Exception\InvalidArgumentException;
use Tmv\WhatsApi\Message\Action\MessageInterface;

class MessageQueue extends \SplQueue
{
    /**
     * @var mixed|MessageInterface
     */
    protected $parked;

    /**
     * @var int
     */
    protected $parkedTime = 0;

    /**
     * Get the last dequeued message
     *
     * @return MessageInterface
     */
    public function getParked()
    {
        return $this->parked;
    }

    /**
     * @return int
     */
    public function getParkedTime()
    {
        return $this->parkedTime;
    }

    /**
     * @return bool
     */
    public function hasParked()
    {
        return $this->getParked() !== null;
    }

    /**
     * @return $this
     */
    public function removeParked()
    {
        $this->parkedTime = 0;
        $this->parked = null;
        return $this;
    }

    /**
     * Return the next message ready to be sent
     * The message will be parked
     *
     * @return mixed|MessageInterface
     */
    public function getNextMessage()
    {
        $ret = parent::dequeue();
        $this->parked = $ret;
        $this->parkedTime = time();
        return $ret;
    }

    /**
     * @param MessageInterface $message
     * @return $this
     */
    public function addMessage(MessageInterface $message)
    {
        $this->enqueue($message);
        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     * @throws \Tmv\WhatsApi\Exception\InvalidArgumentException
     */
    public function enqueue($value)
    {
        if (!$value instanceof MessageInterface) {
            throw new InvalidArgumentException("Values must be an instance of MessageInterface");
        }
        parent::enqueue($value);
        return $this;
    }


}
 