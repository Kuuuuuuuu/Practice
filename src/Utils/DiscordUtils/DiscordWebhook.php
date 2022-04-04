<?php

declare(strict_types=1);

namespace Kohaku\Utils\DiscordUtils;

use Kohaku\Task\AsyncWebhookTask;
use pocketmine\Server;

class DiscordWebhook
{

    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getURL(): string
    {
        return $this->url;
    }

    public function isValid(): bool
    {
        return filter_var($this->url, FILTER_VALIDATE_URL) !== false;
    }

    public function send(DiscordWebhookUtils $message): void
    {
        Server::getInstance()->getAsyncPool()->submitTask(new AsyncWebhookTask($this, $message));
    }
}