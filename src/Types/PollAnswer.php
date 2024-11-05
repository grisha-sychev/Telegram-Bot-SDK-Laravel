<?php

namespace Teg\Types;

class PollAnswer implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
