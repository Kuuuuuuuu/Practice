<?php

declare(strict_types=1);

namespace Kohaku\Core\Utils\DiscordUtils;

use Kohaku\Core\Task\DiscordWebhookTask;
use pocketmine\Server;

class DiscordWebhook
{

    protected string $url;

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
        Server::getInstance()->getAsyncPool()->submitTask(new DiscordWebhookTask($this, $message));
    }
}