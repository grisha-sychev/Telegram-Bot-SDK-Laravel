<?php

namespace Teg\Types;

class VideoChatEnded implements \Teg\Types\Interface\InitObject
{
    private $duration;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->duration = isset($request->duration) ? $request->duration : 0;
    }

    public function getDuration()
    {
        return $this->duration;
    }
}
