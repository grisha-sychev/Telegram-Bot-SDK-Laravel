<?php

namespace Bot\Api;

class Skeleton extends Basic
{
    private function getRequest($method)
    {
        return $this->request()->{'get' . $method}() ?? null;
    }

    public function getMessage()
    {
        return $this->getRequest('Message');
    }

    public function getCallbackQuery()
    {
        return $this->getRequest('CallbackQuery');
    }

    public function getChannelPost()
    {
        return $this->getRequest('ChannelPost');
    }
}
