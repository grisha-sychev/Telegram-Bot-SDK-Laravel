<?php

namespace Teg\Types;

class PaidMediaPreview implements \Teg\Types\Interface\InitObject
{
    private $type;
    private $width;
    private $height;
    private $duration;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->type = 'preview';
        $this->width = isset($request->width) ? $request->width : null;
        $this->height = isset($request->height) ? $request->height : null;
        $this->duration = isset($request->duration) ? $request->duration : null;
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

    public function getDuration()
    {
        return $this->duration;
    }
}
