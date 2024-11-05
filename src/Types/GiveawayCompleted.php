<?php

namespace Teg\Types;

class GiveawayCompleted implements \Teg\Types\Interface\InitObject
{
    private $winner_count;
    private $unclaimed_prize_count;
    private $giveaway_message;
    private $is_star_giveaway;
    
    public function __construct($request)
    {
        $request = (object) $request;
        $this->winner_count = $request->winner_count;
        $this->unclaimed_prize_count = $request->unclaimed_prize_count ?? null;
        $this->giveaway_message = new Message($request->giveaway_message) ?? null;
        $this->is_star_giveaway = $request->is_star_giveaway ?? null;
    }
    
    public function getWinnerCount()
    {
        return $this->winner_count;
    }
    
    public function getUnclaimedPrizeCount()
    {
        return $this->unclaimed_prize_count;
    }
    
    public function getGiveawayMessage()
    {
        return $this->giveaway_message;
    }
    
    public function isStarGiveaway()
    {
        return $this->is_star_giveaway;
    }
}

