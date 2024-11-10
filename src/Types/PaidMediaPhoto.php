<?php

namespace Teg\Types;

class PaidMediaPhoto implements \Teg\Types\Interface\InitObject
{
    private $type;
    private $photo;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->type = 'photo';
        $this->photo = isset($request->photo) ? new PhotoSize($request->photo) : [];
    }

    public function getType()
    {
        return $this->type;
    }

    public function getPhoto()
    {
        return $this->photo;
    }
}
