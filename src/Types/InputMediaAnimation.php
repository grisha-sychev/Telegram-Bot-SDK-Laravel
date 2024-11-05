<?php

namespace Teg\Types;

class InputMediaAnimation implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
