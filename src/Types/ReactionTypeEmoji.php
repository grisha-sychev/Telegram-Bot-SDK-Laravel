<?php

namespace Teg\Types;

class ReactionTypeEmoji implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
