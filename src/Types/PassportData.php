<?php

namespace Teg\Types;

class PassportData implements \Teg\Types\Interface\InitObject
{
    private $data;
    private $credentials;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->data = isset($request->data) ? new EncryptedPassportElement($request->data) : [];
        $this->credentials = isset($request->credentials) ? new EncryptedCredentials($request->credentials) : null;
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
