<?php

namespace Teg\Types;

class MessageOriginHiddenUser implements \Teg\Types\Interface\InitObject
{
    private $type;
    private $date;
    private $sender_user_name;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->type = 'hidden_user';
        $this->date = $request->date ?? null;
        $this->sender_user_name = $request->sender_user_name ?? null;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getSenderUserName()
    {
        return $this->sender_user_name;
    }
}
