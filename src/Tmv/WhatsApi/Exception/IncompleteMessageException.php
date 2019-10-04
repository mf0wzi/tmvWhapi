<?php

namespace Tmv\WhatsApi\Exception;

/**
 * Invalid argument exception
 */
class IncompleteMessageException extends \RuntimeException implements ExceptionInterface
{

    protected $input;

    public function setInput($input)
    {
        $this->input = $input;
    }

    public function getInput()
    {
        return $this->input;
    }
}
