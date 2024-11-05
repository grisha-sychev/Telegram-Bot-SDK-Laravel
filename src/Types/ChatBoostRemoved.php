<?php

namespace Teg\Types;

class ChatBoostRemoved implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
