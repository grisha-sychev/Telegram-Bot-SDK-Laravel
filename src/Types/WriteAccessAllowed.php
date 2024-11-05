<?php

namespace Teg\Types;

class WriteAccessAllowed implements \Teg\Types\Interface\InitObject
{
    private $from_request;
    private $web_app_name;
    private $from_attachment_menu;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->from_request = $request->from_request ?? null;
        $this->web_app_name = $request->web_app_name ?? null;
        $this->from_attachment_menu = $request->from_attachment_menu ?? null;
    }

    public function getFromRequest()
    {
        return $this->from_request;
    }

    public function getWebAppName()
    {
        return $this->web_app_name;
    }

    public function getFromAttachmentMenu()
    {
        return $this->from_attachment_menu;
    }
}
