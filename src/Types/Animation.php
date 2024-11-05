<?php

namespace Teg\Types;

use Teg\Types\Interface\InitObject;

class Animation implements InitObject
{
    private $file_id;
    private $file_unique_id;
    private $width;
    private $height;
    private $duration;
    private $thumbnail;
    private $file_name;
    private $mime_type;
    private $file_size;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->file_id = $request->file_id;
        $this->file_unique_id = $request->file_unique_id;
        $this->width = $request->width;
        $this->height = $request->height;
        $this->duration = $request->duration;
        $this->thumbnail = new PhotoSize($request->thumbnail) ?? null;
        $this->file_name = $request->file_name ?? null;
        $this->mime_type = $request->mime_type ?? null;
        $this->file_size = $request->file_size ?? null;
    }
    
    public function getFileId()
    {
        return $this->file_id;
    }

    public function getFileUniqueId()
    {
        return $this->file_unique_id;
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

    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    public function getFileName()
    {
        return $this->file_name;
    }

    public function getMimeType()
    {
        return $this->mime_type;
    }

    public function getFileSize()
    {
        return $this->file_size;
    }
}
