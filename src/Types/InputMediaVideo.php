<?php

namespace Teg\Types;

class InputMediaVideo implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
