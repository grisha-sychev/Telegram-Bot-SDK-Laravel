<?php

namespace Teg\Types;

class VideoChatParticipantsInvited implements \Teg\Types\Interface\InitObject
{
    private $users;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->users = isset($request->users) ? new User($request->users) : [];
    }

    public function getUsers()
    {
        return $this->users;
    }
}
