<?php

namespace Teg\Types;

/**
 * This object represents a service message about a user boosting a chat.
 */
class ChatBoostAdded implements \Teg\Types\Interface\InitObject
{
    /**
     * @var int Number of boosts added by the user
     */
    private $boost_count;

    /**
     * ChatBoostAdded constructor.
     *
     * @param array $request
     */
    public function __construct($request)
    {
        $request = (object) $request;
        $this->boost_count = isset($request->boost_count) ? (int) $request->boost_count : 0;
    }

    /**
     * Get the number of boosts added by the user.
     *
     * @return int
     */
    public function getBoostCount()
    {
        return $this->boost_count;
    }
}
