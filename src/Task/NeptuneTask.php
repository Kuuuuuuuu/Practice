<?php

declare(strict_types=1);

namespace Kohaku\Task;

use Kohaku\Loader;
use Kohaku\NeptunePlayer;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class NeptuneTask extends Task
{

    private int $tick = 0;

    public function onRun(): void
    {
        $this->tick++;
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player instanceof NeptunePlayer) {
                if ($this->tick % 20 === 0) {
                    $player->updatePlayer();
                }
                $player->updateCPS();
            }
        }
        $this->getRandomSumoArenas();
        $this->updateServer();
    }

    public function getRandomSumoArenas()
    {
        $availableArenas = [];
        foreach (Loader::getInstance()->SumoArenas as $index => $arena) {
            $availableArenas[$index] = $arena;
        }
        foreach ($availableArenas as $index => $arena) {
            if ($arena->phase !== 0 or $arena->setup or count($arena->players) >= 2) {
                unset($availableArenas[$index]);
            }
        }
        $arenasByPlayers = [];
        foreach ($availableArenas as $index => $arena) {
            $arenasByPlayers[$index] = count($arena->players);
        }
        arsort($arenasByPlayers);
        $top = -1;
        $availableArenas = [];
        foreach ($arenasByPlayers as $index => $players) {
            if ($top === -1) {
                $top = $players;
                $availableArenas[] = $index;
            } else {
                if ($top === $players) {
                    $availableArenas[] = $index;
                }
            }
        }
        if (empty($availableArenas)) {
            return null;
        }
        Loader::getInstance()->WaitingSumo = Loader::getInstance()->SumoArenas[$availableArenas[array_rand($availableArenas)]];
    }

    private function updateServer(): void
    {
        if ($this->tick % 20 === 0) {
            Loader::getDeleteBlockHandler()->update();
            if (Loader::getInstance()->Restarted) {
                Loader::getInstance()->RestartTime--;
                if (Loader::getInstance()->RestartTime !== 0 and Loader::getInstance()->RestartTime % 5 === 0) {
                    Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "§cServer will restart in §e" . Loader::getInstance()->RestartTime . "§c seconds");
                } elseif (Loader::getInstance()->RestartTime === 0) {
                    Loader::getInstance()->getServer()->shutdown();
                }
            }
        }
    }
}