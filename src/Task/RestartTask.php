<?php

declare(strict_types=1);

namespace Kohaku\Core\Task;

use Kohaku\Core\Loader;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class RestartTask extends Task
{

    public function onRun(): void
    {
        Loader::getInstance()->RestartTime--;
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            switch (Loader::getInstance()->RestartTime) {
                case 30:
                    $player->sendMessage(Loader::getPrefixCore() . "§cServer will restart in §e30 §cseconds");
                    break;
                case 15:
                    $player->sendMessage(Loader::getPrefixCore() . "§cServer will restart in §e15 §cseconds");
                    break;
                case 10:
                    $player->sendMessage(Loader::getPrefixCore() . "§cServer will restart in §e10 §cseconds");
                    break;
                case 5:
                    $player->sendMessage(Loader::getPrefixCore() . "§cServer will restart in §e5 §cseconds");
                    break;
                case 4:
                    $player->sendMessage(Loader::getPrefixCore() . "§cServer will restart in §e4 §cseconds");
                    break;
                case 3:
                    $player->sendMessage(Loader::getPrefixCore() . "§cServer will restart in §e3 §cseconds");
                    break;
                case 2:
                    $player->sendMessage(Loader::getPrefixCore() . "§cServer will restart in §e2 §cseconds");
                    break;
                case 1:
                    $player->sendMessage(Loader::getPrefixCore() . "§cServer will restart in §e1 §csecond");
                    break;
                case 0:
                    Server::getInstance()->shutdown();
                    $this->getHandler()->cancel();
                    break;
            }
        }
    }
}