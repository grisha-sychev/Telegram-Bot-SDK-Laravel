<?php

namespace Teg\Types;

class User implements \Teg\Types\Interface\InitObject
{
    private int $id;
    private bool $is_bot;
    private string $first_name;
    private ?string $last_name = null;
    private ?string $username = null;
    private ?string $language_code = null;
    private ?bool $is_premium = null;
    private ?bool $added_to_attachment_menu = null;
    private ?bool $can_join_groups = null;
    private ?bool $can_read_all_group_messages = null;
    private ?bool $supports_inline_queries = null;
    private ?bool $can_connect_to_business = null;
    private ?bool $has_main_web_app = null;

    public function __construct($request) {
        $this->id = $request->id ?? 0;
        $this->is_bot = $request->is_bot ?? false;
        $this->first_name = $request->first_name ?? '';
        $this->last_name = $request->last_name ?? null;
        $this->username = $request->username ?? null;
        $this->language_code = $request->language_code ?? null;
        $this->is_premium = $request->is_premium ?? null;
        $this->added_to_attachment_menu = $request->added_to_attachment_menu ?? null;
        $this->can_join_groups = $request->can_join_groups ?? null;
        $this->can_read_all_group_messages = $request->can_read_all_group_messages ?? null;
        $this->supports_inline_queries = $request->supports_inline_queries ?? null;
        $this->can_connect_to_business = $request->can_connect_to_business ?? null;
        $this->has_main_web_app = $request->has_main_web_app ?? null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getIsBot(): bool
    {
        return $this->is_bot;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getLanguageCode(): ?string
    {
        return $this->language_code;
    }

    public function getIsPremium(): ?bool
    {
        return $this->is_premium;
    }

    public function getAddedToAttachmentMenu(): ?bool
    {
        return $this->added_to_attachment_menu;
    }

    public function getCanJoinGroups(): ?bool
    {
        return $this->can_join_groups;
    }

    public function getCanReadAllGroupMessages(): ?bool
    {
        return $this->can_read_all_group_messages;
    }

    public function getSupportsInlineQueries(): ?bool
    {
        return $this->supports_inline_queries;
    }

    public function getCanConnectToBusiness(): ?bool
    {
        return $this->can_connect_to_business;
    }

    public function getHasMainWebApp(): ?bool
    {
        return $this->has_main_web_app;
    }
}
