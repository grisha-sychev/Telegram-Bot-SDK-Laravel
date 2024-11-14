<?php

namespace Teg\Api;

use Teg\Types\CallbackQuery;
use Teg\Types\Message;

class Skeleton extends Basic
{
    public function getMessage()
    {
        return isset($this->request()->message) ? new Message($this->request()->message) : null;
    }

    public function getCallbackQuery()
    {
        return isset($this->request()->callback_query) ? new CallbackQuery($this->request()->callback_query) : null;
    }
}
