<?php

namespace Teg\Types;

class BackgroundTypeChatTheme implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
