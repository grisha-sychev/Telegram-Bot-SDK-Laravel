<?php

namespace Teg\Types;

class Document implements \Teg\Types\Interface\InitObject
{
    private $file_id;
    private $file_unique_id;
    private $thumbnail;
    private $file_name;
    private $mime_type;
    private $file_size;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->file_id = isset($request->file_id) ? $request->file_id : null;
        $this->file_unique_id = isset($request->file_unique_id) ? $request->file_unique_id : null;
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
