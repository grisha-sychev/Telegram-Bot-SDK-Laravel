<?php

namespace Teg\Types;

class BusinessMessagesDeleted implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
