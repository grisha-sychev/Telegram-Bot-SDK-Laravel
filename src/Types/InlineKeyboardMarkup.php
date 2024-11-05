<?php

namespace Teg\Types;

class InlineKeyboardMarkup implements \Teg\Types\Interface\InitObject
{
    /**
     * This object represents an inline keyboard that appears right next to the message it belongs to.
     *
     * @var InlineKeyboardButton[]
     */
    private $inline_keyboard;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->inline_keyboard = new InlineKeyboardButton($request->inline_keyboard) ?? [];
    }

    public function getInlineKeyboard()
    {
        return $this->inline_keyboard;
    }
}
