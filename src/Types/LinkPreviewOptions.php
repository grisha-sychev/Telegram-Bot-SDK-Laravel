<?php

namespace Teg\Types;

class LinkPreviewOptions implements \Teg\Types\Interface\InitObject
{
    private $is_disabled;
    private $url;
    private $prefer_small_media;
    private $prefer_large_media;
    private $show_above_text;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->is_disabled = isset($request->is_disabled) ? $request->is_disabled : null;
        $this->url = isset($request->url) ? $request->url : null;
        $this->prefer_small_media = isset($request->prefer_small_media) ? $request->prefer_small_media : null;
        $this->prefer_large_media = isset($request->prefer_large_media) ? $request->prefer_large_media : null;
        $this->show_above_text = isset($request->show_above_text) ? $request->show_above_text : null;
    }

    public function getIsDisabled()
    {
        return $this->is_disabled;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getPreferSmallMedia()
    {
        return $this->prefer_small_media;
    }

    public function getPreferLargeMedia()
    {
        return $this->prefer_large_media;
    }

    public function getShowAboveText()
    {
        return $this->show_above_text;
    }
}
