<?php

namespace Teg\Types;

class ChatMemberAdministrator implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
