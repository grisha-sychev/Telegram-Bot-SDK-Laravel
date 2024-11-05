<?php

namespace Teg\Types;

class MenuButtonDefault implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
