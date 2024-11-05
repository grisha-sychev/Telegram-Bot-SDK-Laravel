<?php

namespace Teg\Types;

class ChatBoostSourceGiveaway implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
