<?php

namespace Teg\Types;

class Story implements \Teg\Types\Interface\InitObject
{
    private $chat;
    private $id;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->chat = isset($request->chat) ? new Chat($request->chat) : null;
        $this->id = isset($request->id) ? $request->id : null;
    }

    public function getChat()
    {
        return $this->chat;
    }

    public function getId()
    {
        return $this->id;
    }
}
