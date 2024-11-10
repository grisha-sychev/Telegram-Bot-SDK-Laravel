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
        $this->traveler = isset($request->traveler) ? new User($request->traveler) : null;
        $this->watcher = isset($request->watcher) ? new User($request->watcher) : null;
        $this->distance = isset($request->distance) ? $request->distance : null;
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
