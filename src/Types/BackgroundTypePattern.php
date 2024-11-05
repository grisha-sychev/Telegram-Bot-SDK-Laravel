<?php

namespace Teg\Types;

class BackgroundTypePattern implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
