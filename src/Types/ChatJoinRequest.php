<?php

namespace Teg\Types;

class ChatJoinRequest implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
