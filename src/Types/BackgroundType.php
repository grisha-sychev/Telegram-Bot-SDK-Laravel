<?php

namespace Teg\Types;

class BackgroundType implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
