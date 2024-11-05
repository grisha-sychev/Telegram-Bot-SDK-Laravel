<?php

namespace Teg\Types;

class InputPaidMediaVideo implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
