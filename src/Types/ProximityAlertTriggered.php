<?php

namespace Teg\Types;

class ProximityAlertTriggered implements \Teg\Types\Interface\InitObject
{
    private $traveler;
    private $watcher;
    private $distance;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->traveler = new User($request->traveler);
        $this->watcher = new User($request->watcher);
        $this->distance = $request->distance;
    }

    public function getTraveler()
    {
        return $this->traveler;
    }

    public function getWatcher()
    {
        return $this->watcher;
    }

    public function getDistance()
    {
        return $this->distance;
    }
}
