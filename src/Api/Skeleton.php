<?php

namespace Teg\Api;

use Teg\Types\Message;

class Skeleton extends Basic
{
    public function getMessage()
    {
        return isset($this->request()->message) ? new Message($this->request()->message) : null;
    }
}
