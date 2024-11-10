<?php

namespace Teg\Types;

class MaybeInaccessibleMessage implements \Teg\Types\Interface\InitObject
{
    private $message;
    private $inaccessibleMessage;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->message = isset($request->message) ? new Message($request->message) : null;
        $this->inaccessibleMessage = isset($request->inaccessibleMessage) ? new InaccessibleMessage($request->inaccessibleMessage) : null;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getInaccessibleMessage()
    {
        return $this->inaccessibleMessage;
    }
}
