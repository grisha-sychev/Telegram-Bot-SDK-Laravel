<?php

namespace Teg\Types;

class Dice implements \Teg\Types\Interface\InitObject
{
    private $emoji;
    private $value;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->emoji = $request->emoji ?? '';
        $this->value = $request->value ?? 0;
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
