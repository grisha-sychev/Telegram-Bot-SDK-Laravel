<?php

namespace Teg\Types;

class BusinessConnection implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
