<?php

namespace Teg\Types;

class UsersShared implements \Teg\Types\Interface\InitObject
{
    private $request_id;
    private $users;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->request_id = isset($request->request_id) ? $request->request_id : null;
        $this->users = isset($request->users) ? new SharedUser($request->users) : [];
    }

    public function getRequestId()
    {
        return $this->request_id;
    }

    public function getUsers()
    {
        return $this->users;
    }
}
