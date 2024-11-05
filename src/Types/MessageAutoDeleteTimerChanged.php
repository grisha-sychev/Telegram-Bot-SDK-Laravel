<?php

namespace Teg\Types;

class MessageAutoDeleteTimerChanged implements \Teg\Types\Interface\InitObject
{
    private $message_auto_delete_time;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->message_auto_delete_time = $request->message_auto_delete_time ?? null;
    }

    public function getMessageAutoDeleteTime()
    {
        return $this->message_auto_delete_time;
    }
}
