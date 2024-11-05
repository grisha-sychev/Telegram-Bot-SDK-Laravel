<?php

namespace Teg\Types;

class BotCommandScopeAllGroupChats implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
