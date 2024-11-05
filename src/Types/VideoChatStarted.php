<?php

namespace Teg\Types;

class VideoChatStarted implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
