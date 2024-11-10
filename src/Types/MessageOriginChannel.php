<?php

namespace Teg\Types;

class MessageOriginChannel implements \Teg\Types\Interface\InitObject
{
    private $type;
    private $date;
    private $chat;
    private $message_id;
    private $author_signature;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->type = 'channel';
        $this->date = isset($request->date) ? $request->date : null;
        $this->chat = isset($request->chat) ? new Chat($request->chat) : null;
        $this->message_id = isset($request->message_id) ? $request->message_id : null;
        $this->author_signature = isset($request->author_signature) ? $request->author_signature : null;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getChat()
    {
        return $this->chat;
    }

    public function getMessageId()
    {
        return $this->message_id;
    }

    public function getAuthorSignature()
    {
        return $this->author_signature;
    }
}
