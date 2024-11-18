<?php

namespace Teg\Types;

class MaybeInaccessibleMessage implements \Teg\Types\Interface\InitObject
{
    private $message;
    private $inaccessibleMessage;

    public function __construct($request)
    {
        $request = (object) $request;
        
        isset($request) ? new Message($request) : null;
        $this->inaccessibleMessage = isset($request->inaccessibleMessage) ? new InaccessibleMessage($request->inaccessibleMessage) : null;
    }

    public function getInaccessibleMessage()
    {
        return $this->inaccessibleMessage;
    }
}
