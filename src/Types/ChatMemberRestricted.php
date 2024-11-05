<?php

namespace Teg\Types;

class ChatMemberRestricted implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
