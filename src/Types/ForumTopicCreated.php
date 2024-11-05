<?php

namespace Teg\Types;

class ForumTopicCreated implements \Teg\Types\Interface\InitObject
{
    private $name;
    private $icon_color;
    private $icon_custom_emoji_id;
    
    public function __construct($request)
    {
        $request = (object) $request;
        $this->name = $request->name;
        $this->icon_color = $request->icon_color;
        $this->icon_custom_emoji_id = $request->icon_custom_emoji_id ?? null;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getIconColor()
    {
        return $this->icon_color;
    }
    
    public function getIconCustomEmojiId()
    {
        return $this->icon_custom_emoji_id;
    }
    
}
