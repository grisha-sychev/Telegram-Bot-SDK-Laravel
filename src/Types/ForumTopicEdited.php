<?php

namespace Teg\Types;

class ForumTopicEdited implements \Teg\Types\Interface\InitObject
{
    private $name;
    private $icon_custom_emoji_id;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->name = $request->name ?? null;
        $this->icon_custom_emoji_id = $request->icon_custom_emoji_id ?? null;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIconCustomEmojiId()
    {
        return $this->icon_custom_emoji_id;
    }
}
