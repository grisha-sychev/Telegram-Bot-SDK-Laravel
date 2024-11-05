<?php

namespace Teg\Types;

class WebAppInfo implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
