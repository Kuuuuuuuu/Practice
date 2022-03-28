<?php

declare(strict_types=1);

namespace Kohaku\Core\Task;

use Kohaku\Core\HorizonPlayer;
use Kohaku\Core\Loader;
use Kohaku\Core\Utils\DeleteBlocksHandler;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class HorizonTask extends Task
{

    private int $tick = 0;

    public function onRun(): void
    {
        $this->tick++;
        if ($this->tick % 20 === 0) {
            DeleteBlocksHandler::getInstance()->update();
            $this->RestartServer();
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                /* @var HorizonPlayer $player */
                $player->updatePlayer();
            }
        }
        if ($this->tick % 600 === 0) {
            if (Loader::getInstance()->LeaderboardMode === 1) {
                Loader::getInstance()->LeaderboardMode = 2;
            } else {
                Loader::getInstance()->LeaderboardMode = 1;
            }
        }

        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $name = $player->getName();
            if ($player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena()) and $player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBoxingArena())) {
                $player->sendTip("§bCPS: §f" . Loader::$cps->getClicks($player));
            } else if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBoxingArena())) {
                if (isset(Loader::getInstance()->BoxingPoint[$name])) {
                    $point = Loader::getInstance()->BoxingPoint[$name];
                    $opponent = Loader::getInstance()->BoxingPoint[Loader::getInstance()->PlayerOpponent[$name ?? null] ?? null] ?? 0;
                    $player->sendTip("§aYour Points: §f" . $point . " | §cOpponent: §f" . $opponent . " | §bCPS: §f" . Loader::$cps->getClicks($player));
                } else {
                    Loader::getInstance()->BoxingPoint[$name] = 0;
                }
            } else if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
                if (isset(Loader::getInstance()->TimerTask[$name])) {
                    if (Loader::getInstance()->TimerTask[$name] === true) {
                        if (isset(Loader::getInstance()->TimerData[$name])) {
                            Loader::getInstance()->TimerData[$name] += 5;
                        } else {
                            Loader::getInstance()->TimerData[$name] = 0;
                        }
                        $mins = floor(Loader::getInstance()->TimerData[$name] / 6000);
                        $secs = floor((Loader::getInstance()->TimerData[$name] / 100) % 60);
                        $mili = Loader::getInstance()->TimerData[$name] % 100;
                        $player->sendTip("§a" . $mins . " : " . $secs . " : " . $mili);
                    } else {
                        $player->sendTip("§a0 : 0 : 0");
                        Loader::getInstance()->TimerData[$name] = 0;
                    }
                }
            }
        }
    }

    private function RestartServer()
    {
        if (Loader::getInstance()->Restarted) {
            Loader::getInstance()->RestartTime--;
            if (Loader::getInstance()->RestartTime <= 15) {
                Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "§cServer will restart in §e" . Loader::getInstance()->RestartTime . "§c seconds");
            }
            if (Loader::getInstance()->RestartTime <= 0) {
                Loader::getInstance()->getServer()->shutdown();
            }
        }
    }
}