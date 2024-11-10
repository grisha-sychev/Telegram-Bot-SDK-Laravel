<?php

namespace Teg\Types;

class VideoChatScheduled implements \Teg\Types\Interface\InitObject
{
    private $start_date;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->start_date = isset($request->start_date) ? $request->start_date : null;
    }

    public function getStartDate()
    {
        return $this->start_date;
    }
}
