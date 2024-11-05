<?php

namespace Teg\Types;

class UserProfilePhotos implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}
