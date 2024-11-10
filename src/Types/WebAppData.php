<?php

namespace Teg\Types;

class WebAppData implements \Teg\Types\Interface\InitObject
{
    private $data;
    private $button_text;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->data = isset($request->data) ? $request->data : '';
        $this->button_text = isset($request->button_text) ? $request->button_text : '';
    }

    public function getData()
    {
        return $this->data;
    }

    public function getButtonText()
    {
        return $this->button_text;
    }
}
