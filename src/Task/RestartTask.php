<?php

declare(strict_types=1);

namespace Kohaku\Core\Task;

use Kohaku\Core\Loader;
use Kohaku\Core\Utils\DiscordUtils\DiscordWebhook;
use Kohaku\Core\Utils\DiscordUtils\DiscordWebhookUtils;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class RestartTask extends Task
{

    public function onRun(): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            Loader::getInstance()->RestartTime--;
            switch (Loader::getInstance()->RestartTime) {
                case 30:
                    $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§cServer will restart in §e30 §cseconds");
                    $this->notifidiscord();
                    break;
                case 15:
                    $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§cServer will restart in §e15 §cseconds");
                    $this->notifidiscord();
                    break;
                case 10:
                    $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§cServer will restart in §e10 §cseconds");
                    $this->notifidiscord();
                    break;
                case 5:
                    $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§cServer will restart in §e5 §cseconds");
                    $this->notifidiscord();
                    break;
                case 4:
                    $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§cServer will restart in §e4 §cseconds");
                    $this->notifidiscord();
                    break;
                case 3:
                    $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§cServer will restart in §e3 §cseconds");
                    $this->notifidiscord();
                    break;
                case 2:
                    $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§cServer will restart in §e2 §cseconds");
                    $this->notifidiscord();
                    break;
                case 1:
                    $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§cServer will restart in §e1 §csecond");
                    $this->notifidiscord();
                    break;
                case 0:
                    Server::getInstance()->forceShutdown();
                    $this->notifidiscord();
                    break;
            }
        }
    }

    private function notifidiscord()
    {
        $web = new DiscordWebhook(Loader::getInstance()->getConfig()->get("api"));
        $msg = new DiscordWebhookUtils();
        $msg->setContent(">>> " . "Server will restart in " . Loader::getInstance()->RestartTime . " seconds");
        $web->send($msg);
    }
}