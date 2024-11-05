<?php

namespace Teg\Types;

class BackgroundTypeWallpaper implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
