<?php

namespace Teg\Types;

class ChatShared implements \Teg\Types\Interface\InitObject
{
    private $request_id;
    private $chat_id;
    private $title;
    private $username;
    private $photo;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->request_id = isset($request->request_id) ? $request->request_id : null;
        $this->chat_id = isset($request->chat_id) ? $request->chat_id : null;
        $this->title = isset($request->title) ? $request->title : null;
        $this->username = isset($request->username) ? $request->username : null;
        $this->photo = isset($request->photo) ? new PhotoSize($request->photo) : [];
    }

    public function getRequestId()
    {
        return $this->request_id;
    }

    public function getChatId()
    {
        return $this->chat_id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPhoto()
    {
        return $this->photo;
    }
}
