<?php

namespace Teg\\Types;

class BotCommand implements \Teg\\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
