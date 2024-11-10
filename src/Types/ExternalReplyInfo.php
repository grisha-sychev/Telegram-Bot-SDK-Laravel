<?php

namespace Teg\Types;

class ExternalReplyInfo implements \Teg\Types\Interface\InitObject
{
    private $origin;
    private $chat;
    private $message_id;
    private $link_preview_options;
    private $animation;
    private $audio;
    private $document;
    private $paid_media;
    private $photo;
    private $sticker;
    private $story;
    private $video;
    private $video_note;
    private $voice;
    private $has_media_spoiler;
    private $contact;
    private $dice;
    private $game;
    private $giveaway;
    private $giveaway_winners;
    private $invoice;
    private $location;
    private $poll;
    private $venue;

    public function __construct($request)
    {
        $request = (object) $request;

        $this->origin = isset($request->origin) ? new MessageOrigin($request->origin) : null;
        $this->chat = isset($request->chat) ? new Chat($request->chat) : null;
        $this->message_id = isset($request->message_id) ? $request->message_id : null;
        $this->link_preview_options = isset($request->link_preview_options) ? new LinkPreviewOptions($request->link_preview_options) : null;
        $this->animation = isset($request->animation) ? new Animation($request->animation) : null;
        $this->audio = isset($request->audio) ? new Audio($request->audio) : null;
        $this->document = isset($request->document) ? new Document($request->document) : null;
        $this->paid_media = isset($request->paid_media) ? new PaidMediaInfo($request->paid_media) : null;
        $this->photo = isset($request->photo) ? new PhotoSize($request->photo) : null;
        $this->sticker = isset($request->sticker) ? new Sticker($request->sticker) : null;
        $this->story = isset($request->story) ? new Story($request->story) : null;
        $this->video = isset($request->video) ? new Video($request->video) : null;
        $this->video_note = isset($request->video_note) ? new VideoNote($request->video_note) : null;
        $this->voice = isset($request->voice) ? new Voice($request->voice) : null;
        $this->has_media_spoiler = isset($request->has_media_spoiler) ? $request->has_media_spoiler : null;
        $this->contact = isset($request->contact) ? new Contact($request->contact) : null;
        $this->dice = isset($request->dice) ? new Dice($request->dice) : null;
        $this->game = isset($request->game) ? new Game($request->game) : null;
        $this->giveaway = isset($request->giveaway) ? new Giveaway($request->giveaway) : null;
        $this->giveaway_winners = isset($request->giveaway_winners) ? new GiveawayWinners($request->giveaway_winners) : null;
        $this->invoice = isset($request->invoice) ? new Invoice($request->invoice) : null;
        $this->location = isset($request->location) ? new Location($request->location) : null;
        $this->poll = isset($request->poll) ? new Poll($request->poll) : null;
        $this->venue = isset($request->venue) ? new Venue($request->venue) : null;
    }

    public function getOrigin()
    {
        return $this->origin;
    }

    public function getChat()
    {
        return $this->chat;
    }

    public function getMessageId()
    {
        return $this->message_id;
    }

    public function getLinkPreviewOptions()
    {
        return $this->link_preview_options;
    }

    public function getAnimation()
    {
        return $this->animation;
    }

    public function getAudio()
    {
        return $this->audio;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function getPaidMedia()
    {
        return $this->paid_media;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function getSticker()
    {
        return $this->sticker;
    }

    public function getStory()
    {
        return $this->story;
    }

    public function getVideo()
    {
        return $this->video;
    }

    public function getVideoNote()
    {
        return $this->video_note;
    }

    public function getVoice()
    {
        return $this->voice;
    }

    public function getHasMediaSpoiler()
    {
        return $this->has_media_spoiler;
    }

    public function getContact()
    {
        return $this->contact;
    }

    public function getDice()
    {
        return $this->dice;
    }

    public function getGame()
    {
        return $this->game;
    }

    public function getGiveaway()
    {
        return $this->giveaway;
    }

    public function getGiveawayWinners()
    {
        return $this->giveaway_winners;
    }

    public function getInvoice()
    {
        return $this->invoice;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getPoll()
    {
        return $this->poll;
    }

    public function getVenue()
    {
        return $this->venue;
    }
}
