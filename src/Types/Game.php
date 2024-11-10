<?php

namespace Teg\Types;

class Game implements \Teg\Types\Interface\InitObject
{
    private $title;
    private $description;
    private $photo;
    private $text;
    private $text_entities;
    private $animation;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->title = isset($request->title) ? $request->title : '';
        $this->description = isset($request->description) ? $request->description : '';
        $this->photo = isset($request->photo) ? new PhotoSize($request->photo) : [];
        $this->text = isset($request->text) ? $request->text : '';
        $this->text_entities = isset($request->text_entities) ? new MessageEntity($request->text_entities) : [];
        $this->animation = isset($request->animation) ? new Animation($request->animation) : null;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getTextEntities()
    {
        return $this->text_entities;
    }

    public function getAnimation()
    {
        return $this->animation;
    }
}
