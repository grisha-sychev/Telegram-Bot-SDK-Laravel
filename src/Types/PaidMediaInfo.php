<?php

namespace Teg\Types;

class PaidMediaInfo implements \Teg\Types\Interface\InitObject
{
    public $media;

    public function __construct($request)
    {
        $request = (object) $request;

        if (isset($request->preview)) {
            $this->media = new PaidMediaPreview($request->preview);
        } elseif (isset($request->photo)) {
            $this->media = new PaidMediaPhoto($request->photo);
        } elseif (isset($request->video)) {
            $this->media = new PaidMediaVideo($request->video);
        } else {
            throw new \InvalidArgumentException('Invalid media type');
        }
    }
}
