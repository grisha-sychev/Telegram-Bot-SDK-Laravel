<?php

namespace Teg\Types;

class KeyboardButtonRequestChat implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
