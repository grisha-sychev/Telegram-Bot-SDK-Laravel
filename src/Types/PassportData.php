<?php

namespace Teg\Types;

class PassportData implements \Teg\Types\Interface\InitObject
{
    private $data;
    private $credentials;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->data = new EncryptedPassportElement($request->data) ?? [];
        $this->credentials = new EncryptedCredentials($request->credentials) ?? null;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getCredentials()
    {
        return $this->credentials;
    }
}
