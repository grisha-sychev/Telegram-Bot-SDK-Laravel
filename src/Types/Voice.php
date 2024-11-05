<?php

namespace Teg\Types;

class Voice implements \Teg\Types\Interface\InitObject
{
    private $file_id;
    private $file_unique_id;
    private $duration;
    private $mime_type;
    private $file_size;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->file_id = $request->file_id;
        $this->file_unique_id = $request->file_unique_id;
        $this->duration = $request->duration;
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

    public function getDuration()
    {
        return $this->duration;
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
