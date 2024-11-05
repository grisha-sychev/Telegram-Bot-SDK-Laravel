<?php

namespace Teg\Types;

class InputMediaPhoto implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
