<?php

namespace Teg\Types;

class ChatMember implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
