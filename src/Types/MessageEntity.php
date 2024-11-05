<?php

namespace Teg\Types;

class MessageEntity implements \Teg\Types\Interface\InitObject
{
    private $type;
    private $offset;
    private $length;
    private $url;
    private $user;
    private $language;
    private $custom_emoji_id;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->type = $request->type ?? null;
        $this->offset = $request->offset ?? null;
        $this->length = $request->length ?? null;
        $this->url = $request->url ?? null;
        $this->user = new User($request->user) ?? null;
        $this->language = $request->language ?? null;
        $this->custom_emoji_id = $request->custom_emoji_id ?? null;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getCustomEmojiId()
    {
        return $this->custom_emoji_id;
    }
}
