<?php

namespace Teg\Types;

class GiveawayWinners implements \Teg\Types\Interface\InitObject
{
    private $chat;
    private $giveaway_message_id;
    private $winners_selection_date;
    private $winner_count;
    private $winners;
    private $additional_chat_count;
    private $prize_star_count;
    private $premium_subscription_month_count;
    private $unclaimed_prize_count;
    private $only_new_members;
    private $was_refunded;
    private $prize_description;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->chat = new Chat($request->chat);
        $this->giveaway_message_id = $request->giveaway_message_id;
        $this->winners_selection_date = $request->winners_selection_date;
        $this->winner_count = $request->winner_count;
        $this->winners = new User($request->winners);
        $this->additional_chat_count = $request->additional_chat_count ?? null;
        $this->prize_star_count = $request->prize_star_count ?? null;
        $this->premium_subscription_month_count = $request->premium_subscription_month_count ?? null;
        $this->unclaimed_prize_count = $request->unclaimed_prize_count ?? null;
        $this->only_new_members = $request->only_new_members ?? null;
        $this->was_refunded = $request->was_refunded ?? null;
        $this->prize_description = $request->prize_description ?? null;
    }

    public function getChat()
    {
        return $this->chat;
    }

    public function getGiveawayMessageId()
    {
        return $this->giveaway_message_id;
    }

    public function getWinnersSelectionDate()
    {
        return $this->winners_selection_date;
    }

    public function getWinnerCount()
    {
        return $this->winner_count;
    }

    public function getWinners()
    {
        return $this->winners;
    }

    public function getAdditionalChatCount()
    {
        return $this->additional_chat_count;
    }

    public function getPrizeStarCount()
    {
        return $this->prize_star_count;
    }

    public function getPremiumSubscriptionMonthCount()
    {
        return $this->premium_subscription_month_count;
    }

    public function getUnclaimedPrizeCount()
    {
        return $this->unclaimed_prize_count;
    }

    public function getOnlyNewMembers()
    {
        return $this->only_new_members;
    }

    public function getWasRefunded()
    {
        return $this->was_refunded;
    }

    public function getPrizeDescription()
    {
        return $this->prize_description;
    }
}
