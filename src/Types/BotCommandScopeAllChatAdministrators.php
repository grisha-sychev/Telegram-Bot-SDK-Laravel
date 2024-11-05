<?php

namespace Teg\Types;

class BotCommandScopeAllChatAdministrators implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
