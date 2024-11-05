<?php

namespace Teg\Types;

class ChatBackground implements \Teg\Types\Interface\InitObject
{
    private $type;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->type = new BackgroundType($request->type) ?? null;
    }

    /**
     * Get the type of the background.
     *
     * @return BackgroundType
     */
    public function getType()
    {
        return $this->type;
    }
}
