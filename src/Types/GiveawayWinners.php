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
        $this->chat = isset($request->chat) ? new Chat($request->chat) : null;
        $this->giveaway_message_id = isset($request->giveaway_message_id) ? $request->giveaway_message_id : null;
        $this->winners_selection_date = isset($request->winners_selection_date) ? $request->winners_selection_date : null;
        $this->winner_count = isset($request->winner_count) ? $request->winner_count : null;
        $this->winners = isset($request->winners) ? new User($request->winners) : null;
        $this->additional_chat_count = isset($request->additional_chat_count) ? $request->additional_chat_count : null;
        $this->prize_star_count = isset($request->prize_star_count) ? $request->prize_star_count : null;
        $this->premium_subscription_month_count = isset($request->premium_subscription_month_count) ? $request->premium_subscription_month_count : null;
        $this->unclaimed_prize_count = isset($request->unclaimed_prize_count) ? $request->unclaimed_prize_count : null;
        $this->only_new_members = isset($request->only_new_members) ? $request->only_new_members : null;
        $this->was_refunded = isset($request->was_refunded) ? $request->was_refunded : null;
        $this->prize_description = isset($request->prize_description) ? $request->prize_description : null;
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
