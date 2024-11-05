<?php

namespace Teg\Types;

class MessageReactionCountUpdated implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
