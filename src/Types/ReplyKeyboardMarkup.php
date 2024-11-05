<?php

namespace Teg\Types;

class ReplyKeyboardMarkup implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
