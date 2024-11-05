<?php

namespace Teg\Types;

class BusinessOpeningHoursInterval implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
