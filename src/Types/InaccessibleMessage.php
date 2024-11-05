<?php

namespace Teg\Types;

class InaccessibleMessage implements \Teg\Types\Interface\InitObject
{
    private $chat;
    private $message_id;
    private $date;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->chat = new Chat($request->chat) ?? null;
        $this->message_id = $request->message_id ?? null;
        $this->date = $request->date ?? 0;
    }

    public function getChat()
    {
        return $this->chat;
    }

    public function getMessageId()
    {
        return $this->message_id;
    }

    public function getDate()
    {
        return $this->date;
    }
}
