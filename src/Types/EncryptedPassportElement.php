<?php

namespace Teg\Types;

class EncryptedPassportElement implements \Teg\Types\Interface\InitObject
{
    public function __construct($request)
    {
        $request = (object) $request;
    }
}   


