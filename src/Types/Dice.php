<?php

namespace Teg\Types;

class Dice implements \Teg\Types\Interface\InitObject
{
    private $emoji;
    private $value;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->emoji = isset($request->emoji) ? $request->emoji : '';
        $this->value = isset($request->value) ? $request->value : 0;
    }

    public function getEmoji(): string
    {
        return $this->emoji;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
