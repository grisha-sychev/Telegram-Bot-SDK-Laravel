<?php

namespace Teg\Types;

class GiveawayCreated implements \Teg\Types\Interface\InitObject
{
    private $prize_star_count;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->prize_star_count = $request->prize_star_count ?? null;
    }

    public function getPrizeStarCount()
    {
        return $this->prize_star_count;
    }
}
