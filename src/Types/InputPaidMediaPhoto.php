<?php

namespace Teg\Types;

class InputPaidMediaPhoto implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
