<?php

namespace Teg\Types;

class BotCommandScopeChatMember implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
