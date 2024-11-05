<?php

namespace Teg\Types;

class GeneralForumTopicHidden implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
