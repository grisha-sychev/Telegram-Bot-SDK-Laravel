<?php

namespace Teg\Types;

class PhotoSize implements \Teg\Types\Interface\InitObject
{
    private $file_id;
    private $file_unique_id;
    private $width;
    private $height;
    private $file_size;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->file_id = isset($request->file_id) ? $request->file_id : null;
        $this->file_unique_id = isset($request->file_unique_id) ? $request->file_unique_id : null;
        $this->width = isset($request->width) ? $request->width : null;
        $this->height = isset($request->height) ? $request->height : null;
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

    public function getFileSize()
    {
        return $this->file_size;
    }
}
