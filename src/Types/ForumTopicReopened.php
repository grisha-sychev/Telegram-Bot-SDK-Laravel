<?php

namespace Teg\Types;

class ForumTopicReopened implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
