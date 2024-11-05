<?php

namespace Teg\Types;

class Contact implements \Teg\Types\Interface\InitObject
{
    private $phone_number;
    private $first_name;
    private $last_name;
    private $user_id;
    private $vcard;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->phone_number = $request->phone_number ?? null;
        $this->first_name = $request->first_name ?? null;
        $this->last_name = $request->last_name ?? null;
        $this->user_id = $request->user_id ?? null;
        $this->vcard = $request->vcard ?? null;
    }

    public function getPhoneNumber()
    {
        return $this->phone_number;
    }

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getVcard()
    {
        return $this->vcard;
    }
}
