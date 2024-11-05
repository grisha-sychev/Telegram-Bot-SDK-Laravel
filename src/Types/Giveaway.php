<?php

namespace Teg\Types;

class Giveaway implements \Teg\Types\Interface\InitObject
{
    private $chats;
    private $winners_selection_date;
    private $winner_count;
    private $only_new_members;
    private $has_public_winners;
    private $prize_description;
    private $country_codes;
    private $prize_star_count;
    private $premium_subscription_month_count;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->chats = new Chat($request->chats) ?? [];
        $this->winners_selection_date = $request->winners_selection_date ?? 0;
        $this->winner_count = $request->winner_count ?? 0;
        $this->only_new_members = $request->only_new_members ?? false;
        $this->has_public_winners = $request->has_public_winners ?? false;
        $this->prize_description = $request->prize_description ?? '';
        $this->country_codes = $request->country_codes ?? [];
        $this->prize_star_count = $request->prize_star_count ?? 0;
        $this->premium_subscription_month_count = $request->premium_subscription_month_count ?? 0;
    }

    public function getChats()
    {
        return $this->chats;
    }

    public function getWinnersSelectionDate()
    {
        return $this->winners_selection_date;
    }

    public function getWinnerCount()
    {
        return $this->winner_count;
    }

    public function getOnlyNewMembers()
    {
        return $this->only_new_members;
    }

    public function getHasPublicWinners()
    {
        return $this->has_public_winners;
    }

    public function getPrizeDescription()
    {
        return $this->prize_description;
    }

    public function getCountryCodes()
    {
        return $this->country_codes;
    }

    public function getPrizeStarCount()
    {
        return $this->prize_star_count;
    }

    public function getPremiumSubscriptionMonthCount()
    {
        return $this->premium_subscription_month_count;
    }
}
