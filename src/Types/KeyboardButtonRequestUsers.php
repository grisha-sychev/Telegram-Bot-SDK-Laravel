<?php

namespace Teg\Types;

class KeyboardButtonRequestUsers implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
