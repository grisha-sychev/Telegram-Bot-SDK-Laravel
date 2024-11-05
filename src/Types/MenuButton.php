<?php

namespace Teg\Types;

class MenuButton implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
