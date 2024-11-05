<?php

namespace Teg\Types;

class InputMedia implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
