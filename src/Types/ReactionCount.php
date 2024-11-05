<?php

namespace Teg\Types;

class ReactionCount implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
