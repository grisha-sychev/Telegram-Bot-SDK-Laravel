<?php

namespace Teg\Types;

class MessageOriginChat implements \Teg\Types\Interface\InitObject
{
    private $type;
    private $date;
    private $sender_chat;
    private $author_signature;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->type = $request->type ?? 'chat';
        $this->date = $request->date ?? null;
        $this->sender_chat = new Chat($request->sender_chat) ?? null;
        $this->author_signature = $request->author_signature ?? null;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getSenderChat()
    {
        return $this->sender_chat;
    }

    public function getAuthorSignature()
    {
        return $this->author_signature;
    }
}
