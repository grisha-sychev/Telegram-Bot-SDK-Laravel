<?php

namespace Teg\\Types;

class BotCommandScope implements \Teg\\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
