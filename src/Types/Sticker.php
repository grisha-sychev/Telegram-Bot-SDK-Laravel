<?php

namespace Teg\Types;

class Sticker implements \Teg\Types\Interface\InitObject
{
    private $file_id;
    private $file_unique_id;
    private $type;
    private $width;
    private $height;
    private $is_animated;
    private $is_video;
    private $thumbnail;
    private $emoji;
    private $set_name;
    private $premium_animation;
    private $mask_position;
    private $custom_emoji_id;
    private $needs_repainting;
    private $file_size;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->file_id = isset($request->file_id) ? $request->file_id : null;
        $this->file_unique_id = isset($request->file_unique_id) ? $request->file_unique_id : null;
        $this->type = isset($request->type) ? $request->type : null;
        $this->width = isset($request->width) ? $request->width : null;
        $this->height = isset($request->height) ? $request->height : null;
        $this->is_animated = isset($request->is_animated) ? $request->is_animated : null;
        $this->is_video = isset($request->is_video) ? $request->is_video : null;
        $this->thumbnail = isset($request->thumbnail) ? new PhotoSize($request->thumbnail) : null;
        $this->emoji = isset($request->emoji) ? $request->emoji : null;
        $this->set_name = isset($request->set_name) ? $request->set_name : null;
        $this->premium_animation = isset($request->premium_animation) ? new File($request->premium_animation) : null;
        $this->mask_position = isset($request->mask_position) ? new MaskPosition($request->mask_position) : null;
        $this->custom_emoji_id = isset($request->custom_emoji_id) ? $request->custom_emoji_id : null;
        $this->needs_repainting = isset($request->needs_repainting) ? $request->needs_repainting : null;
        $this->file_size = isset($request->file_size) ? $request->file_size : null;
    }

    public function getFileId()
    {
        return $this->file_id;
    }

    public function getFileUniqueId()
    {
        return $this->file_unique_id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getIsAnimated()
    {
        return $this->is_animated;
    }

    public function getIsVideo()
    {
        return $this->is_video;
    }

    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    public function getEmoji()
    {
        return $this->emoji;
    }

    public function getSetName()
    {
        return $this->set_name;
    }

    public function getPremiumAnimation()
    {
        return $this->premium_animation;
    }

    public function getMaskPosition()
    {
        return $this->mask_position;
    }

    public function getCustomEmojiId()
    {
        return $this->custom_emoji_id;
    }

    public function getNeedsRepainting()
    {
        return $this->needs_repainting;
    }

    public function getFileSize()
    {
        return $this->file_size;
    }
}
