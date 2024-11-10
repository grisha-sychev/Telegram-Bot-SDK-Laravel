<?php

namespace Teg\Types;

class Invoice implements \Teg\Types\Interface\InitObject
{
    private $title;
    private $description;
    private $start_parameter;
    private $currency;
    private $total_amount;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->title = isset($request->title) ? $request->title : null;
        $this->description = isset($request->description) ? $request->description : null;
        $this->start_parameter = isset($request->start_parameter) ? $request->start_parameter : null;
        $this->currency = isset($request->currency) ? $request->currency : null;
        $this->total_amount = isset($request->total_amount) ? $request->total_amount : null;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getStartParameter()
    {
        return $this->start_parameter;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getTotalAmount()
    {
        return $this->total_amount;
    }
}
