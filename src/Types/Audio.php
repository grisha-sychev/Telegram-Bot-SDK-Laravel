<?php

namespace Teg\Types;

class Audio implements \Teg\Types\Interface\InitObject
{
    private $file_id;
    private $file_unique_id;
    private $duration;
    private $performer;
    private $title;
    private $file_name;
    private $mime_type;
    private $file_size;
    private $thumbnail;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->file_id = $request->file_id;
        $this->file_unique_id = $request->file_unique_id;
        $this->duration = $request->duration;
        $this->performer = $request->performer ?? null;
        $this->title = $request->title ?? null;
        $this->file_name = $request->file_name ?? null;
        $this->mime_type = $request->mime_type ?? null;
        $this->file_size = $request->file_size ?? null;
        $this->thumbnail = new 	PhotoSize($request->thumbnail) ?? null;
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

    public function getPerformer()
    {
        return $this->performer;
    }

    public function getTitle()
    {
        return $this->title;
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

    public function getThumbnail()
    {
        return $this->thumbnail;
    }
}
