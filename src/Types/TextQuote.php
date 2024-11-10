<?php

namespace Teg\Types;

class TextQuote implements \Teg\Types\Interface\InitObject
{
    private $text;
    private $entities;
    private $position;
    private $is_manual;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->text = isset($request->text) ? $request->text : '';
        $this->entities = isset($request->entities) ? new MessageEntity($request->entities) : [];
        $this->position = isset($request->position) ? $request->position : 0;
        $this->is_manual = isset($request->is_manual) ? $request->is_manual : false;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getEntities()
    {
        return $this->entities;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getIsManual()
    {
        return $this->is_manual;
    }
}
