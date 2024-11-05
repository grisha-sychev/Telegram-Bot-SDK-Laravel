<?php

namespace Teg\Types;

class MessageOriginUser implements \Teg\Types\Interface\InitObject
{
    private $type;
    private $date;
    private $sender_user;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->type = 'user';
        $this->date = $request->date ?? null;
        $this->sender_user = new User($request->sender_user) ?? null;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getSenderUser()
    {
        return $this->sender_user;
    }
}
