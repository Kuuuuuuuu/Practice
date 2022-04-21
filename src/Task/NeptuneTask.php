<?php

declare(strict_types=1);

namespace Kohaku\Task;

use Kohaku\Arena\BotDuelFactory;
use Kohaku\Arena\DuelFactory;
use Kohaku\Loader;
use Kohaku\NeptunePlayer;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class NeptuneTask extends Task
{
    private array $DuelTask = [];
    private int $tick = 0;

    public function __Construct()
    {
        Loader::setCoreTask($this);
    }

    public function onRun(): void
    {
        $this->tick++;
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player instanceof NeptunePlayer) {
                $player->update();
            }
        }
        $this->updateServer();
    }

    private function updateServer(): void
    {
        if ($this->tick % 20 === 0) {
            Loader::getDeleteBlockHandler()->update();
            if (count($this->DuelTask) > 0) {
                foreach ($this->DuelTask as $duel) {
                    if ($duel instanceof DuelFactory or $duel instanceof BotDuelFactory) {
                        $duel->update();
                    }
                }
            }
            if (Loader::getInstance()->Restarted) {
                Loader::getInstance()->RestartTime--;
                if (Loader::getInstance()->RestartTime !== 0 and Loader::getInstance()->RestartTime % 5 === 0) {
                    Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . '§cServer will restart in §e' . Loader::getInstance()->RestartTime . '§c seconds');
                } elseif (Loader::getInstance()->RestartTime <= 0) {
                    foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                        $player->kick('§cServer restarted');
                    }
                } elseif (Loader::getInstance()->RestartTime <= -5) {
                    Loader::getInstance()->getServer()->shutdown();
                }
            }
        }
    }

    public function addBotDuelTask(string $name, BotDuelFactory $duel): void
    {
        $this->DuelTask[$name] = $duel;
    }

    public function removeBotDuelTask(string $name): void
    {
        unset($this->DuelTask[$name]);
    }

    public function addDuelTask(string $name, DuelFactory $duel): void
    {
        $this->DuelTask[$name] = $duel;
    }

    public function removeDuelTask(string $name): void
    {
        unset($this->DuelTask[$name]);
    }

    public function __destruct()
    {
        Loader::setCoreTask(null);
    }
}