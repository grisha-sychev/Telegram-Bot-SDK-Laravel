<?php

namespace Teg\Types;

class VideoNote implements \Teg\Types\Interface\InitObject
{
    private $file_id;
    private $file_unique_id;
    private $length;
    private $duration;
    private $thumbnail;
    private $file_size;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->file_id = isset($request->file_id) ? $request->file_id : null;
        $this->file_unique_id = isset($request->file_unique_id) ? $request->file_unique_id : null;
        $this->length = isset($request->length) ? $request->length : null;
        $this->duration = isset($request->duration) ? $request->duration : null;
        $this->thumbnail = isset($request->thumbnail) ? new PhotoSize($request->thumbnail) : null;
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

    public function getLength()
    {
        return $this->length;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    public function getFileSize()
    {
        return $this->file_size;
    }
}
