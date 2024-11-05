<?php

namespace Teg\Types;

class BotCommandScopeAllPrivateChats implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
