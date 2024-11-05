<?php

namespace Teg\Types;

class PaidMedia implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
