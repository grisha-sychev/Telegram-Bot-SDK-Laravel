<?php

namespace Teg\Types;

class BotShortDescription implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
