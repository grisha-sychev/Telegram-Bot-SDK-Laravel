<?php

namespace Teg\Types;

class LoginUrl implements \Teg\Types\Interface\InitObject
{
    private $url;
    private $forward_text;
    private $bot_username;
    private $request_write_access;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->url = isset($request->url) ? $request->url : null;
        $this->forward_text = isset($request->forward_text) ? $request->forward_text : null;
        $this->bot_username = isset($request->bot_username) ? $request->bot_username : null;
        $this->request_write_access = isset($request->request_write_access) ? $request->request_write_access : null;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getForwardText()
    {
        return $this->forward_text;
    }

    public function getBotUsername()
    {
        return $this->bot_username;
    }

    public function getRequestWriteAccess()
    {
        return $this->request_write_access;
    }
}
