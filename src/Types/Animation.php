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
        $this->file_id = isset($request->file_id) ? $request->file_id : null;
        $this->file_unique_id = isset($request->file_unique_id) ? $request->file_unique_id : null;
        $this->width = isset($request->width) ? $request->width : null;
        $this->height = isset($request->height) ? $request->height : null;
        $this->duration = isset($request->duration) ? $request->duration : null;
        $this->thumbnail = isset($request->thumbnail) ? new PhotoSize($request->thumbnail) : null;
        $this->file_name = isset($request->file_name) ? $request->file_name : null;
        $this->mime_type = isset($request->mime_type) ? $request->mime_type : null;
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
