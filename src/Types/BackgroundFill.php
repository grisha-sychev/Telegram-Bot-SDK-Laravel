<?php

namespace Teg\Types;

class BackgroundFill implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
