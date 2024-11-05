<?php

namespace Teg\Types;

class ChatMemberMember implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
