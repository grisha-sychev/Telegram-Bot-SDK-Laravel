<?php

namespace Teg\Types;

class InlineKeyboardButton implements \Teg\Types\Interface\InitObject
{
    private $text;
    private $url;
    private $callback_data;
    private $web_app;
    private $login_url;
    private $switch_inline_query;
    private $switch_inline_query_current_chat;
    private $switch_inline_query_chosen_chat;
    private $callback_game;
    private $pay;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->text = $request->text ?? null;
        $this->url = $request->url ?? null;
        $this->callback_data = $request->callback_data ?? null;
        $this->web_app = new WebAppInfo($request->web_app) ?? null;
        $this->login_url = new LoginUrl($request->login_url) ?? null;
        $this->switch_inline_query = $request->switch_inline_query ?? null;
        $this->switch_inline_query_current_chat = $request->switch_inline_query_current_chat ?? null;
        $this->switch_inline_query_chosen_chat = new SwitchInlineQueryChosenChat($request->switch_inline_query_chosen_chat) ?? null;
        $this->callback_game = new CallbackGame($request->callback_game) ?? null;
        $this->pay = $request->pay ?? null;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getCallbackData()
    {
        return $this->callback_data;
    }

    public function getWebApp()
    {
        return $this->web_app;
    }

    public function getLoginUrl()
    {
        return $this->login_url;
    }

    public function getSwitchInlineQuery()
    {
        return $this->switch_inline_query;
    }

    public function getSwitchInlineQueryCurrentChat()
    {
        return $this->switch_inline_query_current_chat;
    }

    public function getSwitchInlineQueryChosenChat()
    {
        return $this->switch_inline_query_chosen_chat;
    }

    public function getCallbackGame()
    {
        return $this->callback_game;
    }

    public function getPay()
    {
        return $this->pay;
    }
}
