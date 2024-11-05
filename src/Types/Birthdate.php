<?php

namespace Teg\Types;

class Birthdate implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
