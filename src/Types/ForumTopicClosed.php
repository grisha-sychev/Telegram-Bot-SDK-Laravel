<?php

namespace Teg\Types;

class ForumTopicClosed implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
