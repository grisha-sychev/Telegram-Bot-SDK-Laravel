<?php

namespace Teg\Api;

use Teg\Types\Message;

class Skeleton extends Basic
{
    public function getMessage()
    {
        return new Message($this->request());
    }
}
