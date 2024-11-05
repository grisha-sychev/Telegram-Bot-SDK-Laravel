<?php

namespace Teg\Types;

use Teg\Types\Interface\InitObject;

class Message implements InitObject
{
    private int $message_id;
    private ?int $message_thread_id;
    private ?User $from;
    private ?Chat $sender_chat;
    private ?int $sender_boost_count;
    private ?User $sender_business_bot;
    private int $date;
    private ?string $business_connection_id;
    private Chat $chat;
    private ?MessageOrigin $forward_origin;
    private ?bool $is_topic_message;
    private ?bool $is_automatic_forward;
    private ?Message $reply_to_message;
    private ?ExternalReplyInfo $external_reply;
    private ?TextQuote $quote;
    private ?Story $reply_to_story;
    private ?User $via_bot;
    private ?int $edit_date;
    private ?bool $has_protected_content;
    private ?bool $is_from_offline;
    private ?string $media_group_id;
    private ?string $author_signature;
    private ?string $text;
    private ?array $entities;
    private ?LinkPreviewOptions $link_preview_options;
    private ?string $effect_id;
    private ?Animation $animation;
    private ?Audio $audio;
    private ?Document $document;
    private ?PaidMediaInfo $paid_media;
    private ?array $photo;
    private ?Sticker $sticker;
    private ?Story $story;
    private ?Video $video;
    private ?VideoNote $video_note;
    private ?Voice $voice;
    private ?string $caption;
    private ?array $caption_entities;
    private ?bool $show_caption_above_media;
    private ?bool $has_media_spoiler;
    private ?Contact $contact;
    private ?Dice $dice;
    private ?Game $game;
    private ?Poll $poll;
    private ?Venue $venue;
    private ?Location $location;
    private ?array $new_chat_members;
    private ?User $left_chat_member;
    private ?string $new_chat_title;
    private ?array $new_chat_photo;
    private ?bool $delete_chat_photo;
    private ?bool $group_chat_created;
    private ?bool $supergroup_chat_created;
    private ?bool $channel_chat_created;
    private ?MessageAutoDeleteTimerChanged $message_auto_delete_timer_changed;
    private ?int $migrate_to_chat_id;
    private ?int $migrate_from_chat_id;
    private ?MaybeInaccessibleMessage $pinned_message;
    private ?Invoice $invoice;
    private ?SuccessfulPayment $successful_payment;
    private ?RefundedPayment $refunded_payment;
    private ?UsersShared $users_shared;
    private ?ChatShared $chat_shared;
    private ?string $connected_website;
    private ?WriteAccessAllowed $write_access_allowed;
    private ?PassportData $passport_data;
    private ?ProximityAlertTriggered $proximity_alert_triggered;
    private ?ChatBoostAdded $boost_added;
    private ?ChatBackground $chat_background_set;
    private ?ForumTopicCreated $forum_topic_created;
    private ?ForumTopicEdited $forum_topic_edited;
    private ?ForumTopicClosed $forum_topic_closed;
    private ?ForumTopicReopened $forum_topic_reopened;
    private ?GeneralForumTopicHidden $general_forum_topic_hidden;
    private ?GeneralForumTopicUnhidden $general_forum_topic_unhidden;
    private ?GiveawayCreated $giveaway_created;
    private ?Giveaway $giveaway;
    private ?GiveawayWinners $giveaway_winners;
    private ?GiveawayCompleted $giveaway_completed;
    private ?VideoChatScheduled $video_chat_scheduled;
    private ?VideoChatStarted $video_chat_started;
    private ?VideoChatEnded $video_chat_ended;
    private ?VideoChatParticipantsInvited $video_chat_participants_invited;
    private ?WebAppData $web_app_data;
    private ?InlineKeyboardMarkup $reply_markup;

    public function __construct($request)
    {
        $request = (object) $request;
        $this->message_id = $request->message_id ?? 0;
        $this->message_thread_id = $request->message_thread_id ?? null;
        $this->from = isset($request->from) ? new User($request->from) : null;
        $this->sender_chat = isset($request->sender_chat) ? new Chat($request->sender_chat) : null;
        $this->sender_boost_count = $request->sender_boost_count ?? null;
        $this->sender_business_bot = isset($request->sender_business_bot) ? new User($request->sender_business_bot) : null;
        $this->date = $request->date ?? 0;
        $this->business_connection_id = $request->business_connection_id ?? null;
        $this->chat = new Chat($request->chat);
        $this->forward_origin = isset($request->forward_origin) ? new MessageOrigin($request->forward_origin) : null;
        $this->is_topic_message = $request->is_topic_message ?? null;
        $this->is_automatic_forward = $request->is_automatic_forward ?? null;
        $this->reply_to_message = isset($request->reply_to_message) ? new Message($request->reply_to_message) : null;
        $this->external_reply = isset($request->external_reply) ? new ExternalReplyInfo($request->external_reply) : null;
        $this->quote = isset($request->quote) ? new TextQuote($request->quote) : null;
        $this->reply_to_story = isset($request->reply_to_story) ? new Story($request->reply_to_story) : null;
        $this->via_bot = isset($request->via_bot) ? new User($request->via_bot) : null;
        $this->edit_date = $request->edit_date ?? null;
        $this->has_protected_content = $request->has_protected_content ?? null;
        $this->is_from_offline = $request->is_from_offline ?? null;
        $this->media_group_id = $request->media_group_id ?? null;
        $this->author_signature = $request->author_signature ?? null;
        $this->text = $request->text ?? null;
        $this->entities = isset($request->entities) ? array_map(fn($entity) => new MessageEntity($entity), $request->entities) : null;
        $this->link_preview_options = isset($request->link_preview_options) ? new LinkPreviewOptions($request->link_preview_options) : null;
        $this->effect_id = $request->effect_id ?? null;
        $this->animation = isset($request->animation) ? new Animation($request->animation) : null;
        $this->audio = isset($request->audio) ? new Audio($request->audio) : null;
        $this->document = isset($request->document) ? new Document($request->document) : null;
        $this->paid_media = isset($request->paid_media) ? new PaidMediaInfo($request->paid_media) : null;
        $this->photo = isset($request->photo) ? array_map(fn($photo) => new PhotoSize($photo), $request->photo) : null;
        $this->sticker = isset($request->sticker) ? new Sticker($request->sticker) : null;
        $this->story = isset($request->story) ? new Story($request->story) : null;
        $this->video = isset($request->video) ? new Video($request->video) : null;
        $this->video_note = isset($request->video_note) ? new VideoNote($request->video_note) : null;
        $this->voice = isset($request->voice) ? new Voice($request->voice) : null;
        $this->caption = $request->caption ?? null;
        $this->caption_entities = isset($request->caption_entities) ? array_map(fn($entity) => new MessageEntity($entity), $request->caption_entities) : null;
        $this->show_caption_above_media = $request->show_caption_above_media ?? null;
        $this->has_media_spoiler = $request->has_media_spoiler ?? null;
        $this->contact = isset($request->contact) ? new Contact($request->contact) : null;
        $this->dice = isset($request->dice) ? new Dice($request->dice) : null;
        $this->game = isset($request->game) ? new Game($request->game) : null;
        $this->poll = isset($request->poll) ? new Poll($request->poll) : null;
        $this->venue = isset($request->venue) ? new Venue($request->venue) : null;
        $this->location = isset($request->location) ? new Location($request->location) : null;
        $this->new_chat_members = isset($request->new_chat_members) ? array_map(fn($user) => new User($user), $request->new_chat_members) : null;
        $this->left_chat_member = isset($request->left_chat_member) ? new User($request->left_chat_member) : null;
        $this->new_chat_title = $request->new_chat_title ?? null;
        $this->new_chat_photo = isset($request->new_chat_photo) ? array_map(fn($photo) => new PhotoSize($photo), $request->new_chat_photo) : null;
        $this->delete_chat_photo = $request->delete_chat_photo ?? null;
        $this->group_chat_created = $request->group_chat_created ?? null;
        $this->supergroup_chat_created = $request->supergroup_chat_created ?? null;
        $this->channel_chat_created = $request->channel_chat_created ?? null;
        $this->message_auto_delete_timer_changed = isset($request->message_auto_delete_timer_changed) ? new MessageAutoDeleteTimerChanged($request->message_auto_delete_timer_changed) : null;
        $this->migrate_to_chat_id = $request->migrate_to_chat_id ?? null;
        $this->migrate_from_chat_id = $request->migrate_from_chat_id ?? null;
        $this->pinned_message = isset($request->pinned_message) ? new MaybeInaccessibleMessage($request->pinned_message) : null;
        $this->invoice = isset($request->invoice) ? new Invoice($request->invoice) : null;
        $this->successful_payment = isset($request->successful_payment) ? new SuccessfulPayment($request->successful_payment) : null;
        $this->refunded_payment = isset($request->refunded_payment) ? new RefundedPayment($request->refunded_payment) : null;
        $this->users_shared = isset($request->users_shared) ? new UsersShared($request->users_shared) : null;
        $this->chat_shared = isset($request->chat_shared) ? new ChatShared($request->chat_shared) : null;
        $this->connected_website = $request->connected_website ?? null;
        $this->write_access_allowed = isset($request->write_access_allowed) ? new WriteAccessAllowed($request->write_access_allowed) : null;
        $this->passport_data = isset($request->passport_data) ? new PassportData($request->passport_data) : null;
        $this->proximity_alert_triggered = isset($request->proximity_alert_triggered) ? new ProximityAlertTriggered($request->proximity_alert_triggered) : null;
        $this->boost_added = isset($request->boost_added) ? new ChatBoostAdded($request->boost_added) : null;
        $this->chat_background_set = isset($request->chat_background_set) ? new ChatBackground($request->chat_background_set) : null;
        $this->forum_topic_created = isset($request->forum_topic_created) ? new ForumTopicCreated($request->forum_topic_created) : null;
        $this->forum_topic_edited = isset($request->forum_topic_edited) ? new ForumTopicEdited($request->forum_topic_edited) : null;
        $this->forum_topic_closed = isset($request->forum_topic_closed) ? new ForumTopicClosed($request->forum_topic_closed) : null;
        $this->forum_topic_reopened = isset($request->forum_topic_reopened) ? new ForumTopicReopened($request->forum_topic_reopened) : null;
        $this->general_forum_topic_hidden = isset($request->general_forum_topic_hidden) ? new GeneralForumTopicHidden($request->general_forum_topic_hidden) : null;
        $this->general_forum_topic_unhidden = isset($request->general_forum_topic_unhidden) ? new GeneralForumTopicUnhidden($request->general_forum_topic_unhidden) : null;
        $this->giveaway_created = isset($request->giveaway_created) ? new GiveawayCreated($request->giveaway_created) : null;
        $this->giveaway = isset($request->giveaway) ? new Giveaway($request->giveaway) : null;
        $this->giveaway_winners = isset($request->giveaway_winners) ? new GiveawayWinners($request->giveaway_winners) : null;
        $this->giveaway_completed = isset($request->giveaway_completed) ? new GiveawayCompleted($request->giveaway_completed) : null;
        $this->video_chat_scheduled = isset($request->video_chat_scheduled) ? new VideoChatScheduled($request->video_chat_scheduled) : null;
        $this->video_chat_started = isset($request->video_chat_started) ? new VideoChatStarted($request->video_chat_started) : null;
        $this->video_chat_ended = isset($request->video_chat_ended) ? new VideoChatEnded($request->video_chat_ended) : null;
        $this->video_chat_participants_invited = isset($request->video_chat_participants_invited) ? new VideoChatParticipantsInvited($request->video_chat_participants_invited) : null;
        $this->web_app_data = isset($request->web_app_data) ? new WebAppData($request->web_app_data) : null;
        $this->reply_markup = isset($request->reply_markup) ? new InlineKeyboardMarkup($request->reply_markup) : null;
    }

    public function getMessageId(): int
    {
        return $this->message_id;
    }

    public function getMessageThreadId(): ?int
    {
        return $this->message_thread_id;
    }

    public function getFrom(): ?User
    {
        return $this->from;
    }

    public function getSenderChat(): ?Chat
    {
        return $this->sender_chat;
    }

    public function getSenderBoostCount(): ?int
    {
        return $this->sender_boost_count;
    }

    public function getSenderBusinessBot(): ?User
    {
        return $this->sender_business_bot;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function getBusinessConnectionId(): ?string
    {
        return $this->business_connection_id;
    }

    public function getChat(): Chat
    {
        return $this->chat;
    }

    public function getForwardOrigin(): ?MessageOrigin
    {
        return $this->forward_origin;
    }

    public function getIsTopicMessage(): ?bool
    {
        return $this->is_topic_message;
    }

    public function getIsAutomaticForward(): ?bool
    {
        return $this->is_automatic_forward;
    }

    public function getReplyToMessage(): ?Message
    {
        return $this->reply_to_message;
    }

    public function getExternalReply(): ?ExternalReplyInfo
    {
        return $this->external_reply;
    }

    public function getQuote(): ?TextQuote
    {
        return $this->quote;
    }

    public function getReplyToStory(): ?Story
    {
        return $this->reply_to_story;
    }

    public function getViaBot(): ?User
    {
        return $this->via_bot;
    }

    public function getEditDate(): ?int
    {
        return $this->edit_date;
    }

    public function getHasProtectedContent(): ?bool
    {
        return $this->has_protected_content;
    }

    public function getIsFromOffline(): ?bool
    {
        return $this->is_from_offline;
    }

    public function getMediaGroupId(): ?string
    {
        return $this->media_group_id;
    }

    public function getAuthorSignature(): ?string
    {
        return $this->author_signature;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getEntities(): ?array
    {
        return $this->entities;
    }

    public function getLinkPreviewOptions(): ?LinkPreviewOptions
    {
        return $this->link_preview_options;
    }

    public function getEffectId(): ?string
    {
        return $this->effect_id;
    }

    public function getAnimation(): ?Animation
    {
        return $this->animation;
    }

    public function getAudio(): ?Audio
    {
        return $this->audio;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function getPaidMedia(): ?PaidMediaInfo
    {
        return $this->paid_media;
    }

    public function getPhoto(): ?array
    {
        return $this->photo;
    }

    public function getSticker(): ?Sticker
    {
        return $this->sticker;
    }

    public function getStory(): ?Story
    {
        return $this->story;
    }

    public function getVideo(): ?Video
    {
        return $this->video;
    }

    public function getVideoNote(): ?VideoNote
    {
        return $this->video_note;
    }

    public function getVoice(): ?Voice
    {
        return $this->voice;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function getCaptionEntities(): ?array
    {
        return $this->caption_entities;
    }

    public function getShowCaptionAboveMedia(): ?bool
    {
        return $this->show_caption_above_media;
    }

    public function getHasMediaSpoiler(): ?bool
    {
        return $this->has_media_spoiler;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function getDice(): ?Dice
    {
        return $this->dice;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function getPoll(): ?Poll
    {
        return $this->poll;
    }

    public function getVenue(): ?Venue
    {
        return $this->venue;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function getNewChatMembers(): ?array
    {
        return $this->new_chat_members;
    }

    public function getLeftChatMember(): ?User
    {
        return $this->left_chat_member;
    }

    public function getNewChatTitle(): ?string
    {
        return $this->new_chat_title;
    }

    public function getNewChatPhoto(): ?array
    {
        return $this->new_chat_photo;
    }

    public function getDeleteChatPhoto(): ?bool
    {
        return $this->delete_chat_photo;
    }

    public function getGroupChatCreated(): ?bool
    {
        return $this->group_chat_created;
    }

    public function getSupergroupChatCreated(): ?bool
    {
        return $this->supergroup_chat_created;
    }

    public function getChannelChatCreated(): ?bool
    {
        return $this->channel_chat_created;
    }

    public function getMessageAutoDeleteTimerChanged(): ?MessageAutoDeleteTimerChanged
    {
        return $this->message_auto_delete_timer_changed;
    }

    public function getMigrateToChatId(): ?int
    {
        return $this->migrate_to_chat_id;
    }

    public function getMigrateFromChatId(): ?int
    {
        return $this->migrate_from_chat_id;
    }

    public function getPinnedMessage(): ?MaybeInaccessibleMessage
    {
        return $this->pinned_message;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function getSuccessfulPayment(): ?SuccessfulPayment
    {
        return $this->successful_payment;
    }

    public function getRefundedPayment(): ?RefundedPayment
    {
        return $this->refunded_payment;
    }

    public function getUsersShared(): ?UsersShared
    {
        return $this->users_shared;
    }

    public function getChatShared(): ?ChatShared
    {
        return $this->chat_shared;
    }

    public function getConnectedWebsite(): ?string
    {
        return $this->connected_website;
    }

    public function getWriteAccessAllowed(): ?WriteAccessAllowed
    {
        return $this->write_access_allowed;
    }

    public function getPassportData(): ?PassportData
    {
        return $this->passport_data;
    }

    public function getProximityAlertTriggered(): ?ProximityAlertTriggered
    {
        return $this->proximity_alert_triggered;
    }

    public function getBoostAdded(): ?ChatBoostAdded
    {
        return $this->boost_added;
    }

    public function getChatBackgroundSet(): ?ChatBackground
    {
        return $this->chat_background_set;
    }

    public function getForumTopicCreated(): ?ForumTopicCreated
    {
        return $this->forum_topic_created;
    }

    public function getForumTopicEdited(): ?ForumTopicEdited
    {
        return $this->forum_topic_edited;
    }

    public function getForumTopicClosed(): ?ForumTopicClosed
    {
        return $this->forum_topic_closed;
    }

    public function getForumTopicReopened(): ?ForumTopicReopened
    {
        return $this->forum_topic_reopened;
    }

    public function getGeneralForumTopicHidden(): ?GeneralForumTopicHidden
    {
        return $this->general_forum_topic_hidden;
    }

    public function getGeneralForumTopicUnhidden(): ?GeneralForumTopicUnhidden
    {
        return $this->general_forum_topic_unhidden;
    }

    public function getGiveawayCreated(): ?GiveawayCreated
    {
        return $this->giveaway_created;
    }

    public function getGiveaway(): ?Giveaway
    {
        return $this->giveaway;
    }

    public function getGiveawayWinners(): ?GiveawayWinners
    {
        return $this->giveaway_winners;
    }

    public function getGiveawayCompleted(): ?GiveawayCompleted
    {
        return $this->giveaway_completed;
    }

    public function getVideoChatScheduled(): ?VideoChatScheduled
    {
        return $this->video_chat_scheduled;
    }

    public function getVideoChatStarted(): ?VideoChatStarted
    {
        return $this->video_chat_started;
    }

    public function getVideoChatEnded(): ?VideoChatEnded
    {
        return $this->video_chat_ended;
    }

    public function getVideoChatParticipantsInvited(): ?VideoChatParticipantsInvited
    {
        return $this->video_chat_participants_invited;
    }

    public function getWebAppData(): ?WebAppData
    {
        return $this->web_app_data;
    }

    public function getReplyMarkup(): ?InlineKeyboardMarkup
    {
        return $this->reply_markup;
    }
}
