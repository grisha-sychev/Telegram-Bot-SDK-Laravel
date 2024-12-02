<?php

namespace Teg\Api;

class Skeleton extends Basic
{
    public function getMessage()
    {
        if ($this->request()->getMessage()) {
            return $this->request()->getMessage();
        }

        return;
    }

    public function getCallbackQuery()
    {
        if ($this->request()->getCallbackQuery()) {
            return $this->request()->getCallbackQuery();
        }
        
        return;
    }

    public function getChannelPost()
    {

        if ($this->request()->getChannelPost()) {
            return $this->request()->getChannelPost();
        }
        
        return;
    }
}
