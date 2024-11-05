<?php

namespace Teg\Types;

class BusinessLocation implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
