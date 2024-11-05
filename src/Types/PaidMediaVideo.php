<?php

namespace Teg\Types;

class PaidMediaVideo implements \Teg\Types\Interface\InitObject
{
    private $type;
    private $video;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->type = 'video';
        $this->video = new Video($request->video) ?? null;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getVideo()
    {
        return $this->video;
    }
}
