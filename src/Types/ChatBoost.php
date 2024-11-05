<?php

namespace Teg\Types;

class ChatBoost implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
