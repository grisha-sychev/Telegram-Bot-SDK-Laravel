<?php

namespace Teg\Types;

class BackgroundFillGradient implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
