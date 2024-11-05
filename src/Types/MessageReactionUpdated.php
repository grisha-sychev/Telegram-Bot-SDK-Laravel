<?php

namespace Teg\Types;

class MessageReactionUpdated implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
