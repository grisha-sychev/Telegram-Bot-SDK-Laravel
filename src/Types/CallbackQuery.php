<?php

namespace Teg\Types;

class CallbackQuery implements \Teg\Types\Interface\InitObject
{
    private $id;
    private $from;
    private $message;
    private $inline_message_id;
    private $chat_instance;
    private $data;
    private $game_short_name;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->id = isset($request->id) ? $request->id : null;
        $this->from = isset($request->from) ? new User($request->from) : null;
        $this->message = isset($request->message) ? new MaybeInaccessibleMessage($request->message) : null;
        $this->inline_message_id = isset($request->inline_message_id) ? $request->inline_message_id : null;
        $this->chat_instance = isset($request->chat_instance) ? $request->chat_instance : null;
        $this->data = isset($request->data) ? $request->data : null;
        $this->game_short_name = isset($request->game_short_name) ? $request->game_short_name : null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFrom(): ?User
    {
        return $this->from;
    }

    public function getMessage(): ?MaybeInaccessibleMessage
    {
        return $this->message;
    }

    public function getInlineMessageId()
    {
        return $this->inline_message_id;
    }

    public function getChatInstance()
    {
        return $this->chat_instance;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getGameShortName()
    {
        return $this->game_short_name;
    }
}
