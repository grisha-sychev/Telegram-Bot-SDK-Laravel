<?php

namespace Teg\Types;

class KeyboardButton implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
