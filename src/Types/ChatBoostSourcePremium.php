<?php

namespace Teg\Types;

class ChatBoostSourcePremium implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
