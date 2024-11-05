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
        $this->file_id = $request->file_id;
        $this->file_unique_id = $request->file_unique_id;
        $this->width = $request->width;
        $this->height = $request->height;
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

    public function getFileSize()
    {
        return $this->file_size;
    }
}
