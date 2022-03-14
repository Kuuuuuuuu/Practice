<?php

declare(strict_types=1);

namespace Kohaku\Core\Utils\DiscordUtils;

use JsonSerializable;
use ReturnTypeWillChange;

class DiscordWebhookUtils implements JsonSerializable
{

    protected array $data = [];

    public function setContent(string $content): void
    {
        $this->data["content"] = $content;
    }

    public function getContent(): ?string
    {
        return $this->data["content"];
    }

    public function getUsername(): ?string
    {
        return $this->data["username"];
    }

    public function setUsername(string $username): void
    {
        $this->data["username"] = $username;
    }

    public function getAvatarURL(): ?string
    {
        return $this->data["avatar_url"];
    }

    public function setAvatarURL(string $avatarURL): void
    {
        $this->data["avatar_url"] = $avatarURL;
    }

    public function addEmbed(DiscordWebhookEmbed $embed): void
    {
        if (!empty(($arr = $embed->asArray()))) {
            $this->data["embeds"][] = $arr;
        }
    }

    public function setTextToSpeech(bool $ttsEnabled): void
    {
        $this->data["tts"] = $ttsEnabled;
    }

    #[ReturnTypeWillChange] public function jsonSerialize()
    {
        return $this->data;
    }
}