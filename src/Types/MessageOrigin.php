<?php

namespace Teg\Types;

class MessageOrigin implements \Teg\Types\Interface\InitObject
{
    protected $request;

    public function __construct($request)
    {
        $this->request = (object) $request;
    }

    public function getOrigin()
    {
        if (isset($this->request->user)) {
            return new MessageOriginUser($this->request->user);
        } elseif (isset($this->request->hidden_user)) {
            return new MessageOriginHiddenUser($this->request->hidden_user);
        } elseif (isset($this->request->chat)) {
            return new MessageOriginChat($this->request->chat);
        } elseif (isset($this->request->channel)) {
            return new MessageOriginChannel($this->request->channel);
        } else {
            throw new \Exception("Unknown message origin");
        }
    }
}
