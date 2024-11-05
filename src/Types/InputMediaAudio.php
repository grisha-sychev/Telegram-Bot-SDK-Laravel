<?php

namespace Teg\Types;

class InputMediaAudio implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
