<?php

namespace Teg\Types;

class Location implements \Teg\Types\Interface\InitObject
{
    private $latitude;
    private $longitude;
    private $horizontal_accuracy;
    private $live_period;
    private $heading;
    private $proximity_alert_radius;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->latitude = isset($request->latitude) ? $request->latitude : null;
        $this->longitude = isset($request->longitude) ? $request->longitude : null;
        $this->horizontal_accuracy = isset($request->horizontal_accuracy) ? $request->horizontal_accuracy : null;
        $this->live_period = isset($request->live_period) ? $request->live_period : null;
        $this->heading = isset($request->heading) ? $request->heading : null;
        $this->proximity_alert_radius = isset($request->proximity_alert_radius) ? $request->proximity_alert_radius : null;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function getHorizontalAccuracy()
    {
        return $this->horizontal_accuracy;
    }

    public function getLivePeriod()
    {
        return $this->live_period;
    }

    public function getHeading()
    {
        return $this->heading;
    }

    public function getProximityAlertRadius()
    {
        return $this->proximity_alert_radius;
    }
}
