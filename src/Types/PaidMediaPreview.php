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
        $this->width = $request->width ?? null;
        $this->height = $request->height ?? null;
        $this->duration = $request->duration ?? null;
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
