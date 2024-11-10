<?php

namespace Teg\Types;

class Venue implements \Teg\Types\Interface\InitObject
{
    private $location;
    private $title;
    private $address;
    private $foursquare_id;
    private $foursquare_type;
    private $google_place_id;
    private $google_place_type;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->location = isset($request->location) ? new Location($request->location) : null;
        $this->title = isset($request->title) ? $request->title : null;
        $this->address = isset($request->address) ? $request->address : null;
        $this->foursquare_id = isset($request->foursquare_id) ? $request->foursquare_id : null;
        $this->foursquare_type = isset($request->foursquare_type) ? $request->foursquare_type : null;
        $this->google_place_id = isset($request->google_place_id) ? $request->google_place_id : null;
        $this->google_place_type = isset($request->google_place_type) ? $request->google_place_type : null;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getFoursquareId()
    {
        return $this->foursquare_id;
    }

    public function getFoursquareType()
    {
        return $this->foursquare_type;
    }

    public function getGooglePlaceId()
    {
        return $this->google_place_id;
    }

    public function getGooglePlaceType()
    {
        return $this->google_place_type;
    }
}
