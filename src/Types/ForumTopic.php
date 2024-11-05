<?php

namespace Teg\Types;

class ForumTopic implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
