<?php

namespace Teg\Types;

class ChatMemberOwner implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
