<?php

namespace Teg\Types;

class SwitchInlineQueryChosenChat implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
