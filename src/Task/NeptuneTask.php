<?php

declare(strict_types=1);

namespace Kuu\Task;

use Kuu\Arena\BotDuelFactory;
use Kuu\Arena\DuelFactory;
use Kuu\Loader;
use Kuu\NeptunePlayer;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class NeptuneTask extends Task
{
    private array $DuelTask = [];
    private int $tick = 0;

    public function __construct()
    {
        Loader::setCoreTask($this);
    }

    public function onRun(): void
    {
        $this->tick++;
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (!$player instanceof NeptunePlayer) {
                return;
            }
            $player->update();
        }
        $this->updateServer();
    }

    private function updateServer(): void
    {
        if ($this->tick % 20 === 0) {
            Loader::getDeleteBlockHandler()->update();
            foreach ($this->DuelTask as $duel) {
                if (!$duel instanceof DuelFactory && !$duel instanceof BotDuelFactory) {
                    return;
                }
                $duel->update();
            }
        }
    }

    public function removeDuelTask(string $name): void
    {
        unset($this->DuelTask[$name]);
    }

    public function addDuelTask(string $name, DuelFactory|BotDuelFactory $duel): void
    {
        $this->DuelTask[$name] = $duel;
    }

    public function __destruct()
    {
        Loader::setCoreTask(null);
    }
}