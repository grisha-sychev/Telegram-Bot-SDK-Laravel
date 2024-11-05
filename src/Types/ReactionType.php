<?php

namespace Teg\Types;

class ReactionType implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
