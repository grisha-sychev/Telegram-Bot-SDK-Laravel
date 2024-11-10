<?php

namespace Teg\Types;

class BotCommandScopeChatAdministrators implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
