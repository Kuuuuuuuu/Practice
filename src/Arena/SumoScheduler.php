<?php

declare(strict_types=1);

namespace Kohaku\Core\Arena;

use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ArenaUtils;
use pocketmine\scheduler\Task;

class SumoScheduler extends Task
{

    private SumoHandler $plugin;
    private int $startTime = 4;
    private int|float $gameTime = 5 * 30;

    public function __construct(SumoHandler $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onRun(): void
    {
        if (!$this->plugin->setup) {
            if ($this->plugin->phase === SumoHandler::PHASE_LOBBY) {
                foreach ($this->plugin->players as $player) {
                    Loader::getInstance()->inSumo[$player->getName()] = true;
                    Loader::getInstance()->SumoTimer[$player->getName()] = $this->gameTime;
                    if (isset(Loader::getInstance()->inSumo[$player->getName()]) and Loader::getInstance()->inSumo[$player->getName()] === false) {
                        $this->plugin->disconnectPlayer($player);
                    }
                }
                if (count($this->plugin->players) >= 2) {
                    if ($this->startTime > 0) {
                        $this->startTime--;
                        foreach ($this->plugin->players as $player) {
                            $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§cThe game will start in §e" . $this->startTime . "§c seconds!");
                        }
                    }
                    if ($this->startTime == 0) {
                        $this->plugin->startGame();
                    }
                } else {
                    $this->startTime = 4;
                }
            }
            // don't use to switch it make this one slower than if else
            if ($this->plugin->phase === SumoHandler::PHASE_GAME) {
                if ($this->gameTime > 0) {
                    $this->gameTime--;
                }
                if ($this->gameTime < 0) {
                    $this->plugin->startRestart();
                }
                foreach ($this->plugin->players as $player) {
                    if (isset(Loader::getInstance()->inSumo[$player->getName()]) and Loader::getInstance()->inSumo[$player->getName()] === false) {
                        $this->plugin->disconnectPlayer($player);
                    }
                    Loader::getInstance()->SumoTimer[$player->getName()] = $this->gameTime;
                }
                if ($this->plugin->checkEnd()) {
                    $this->plugin->startRestart();
                }
            }
            if ($this->plugin->phase === SumoHandler::PHASE_RESTART) {
                foreach ($this->plugin->players as $player) {
                    $player->teleport($this->plugin->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                    $player->getInventory()->clearAll();
                    $player->getArmorInventory()->clearAll();
                    $player->getEffects()->clear();
                    ArenaUtils::getInstance()->GiveItem($player);
                    $player->setGamemode($this->plugin->plugin->getServer()->getGamemode());
                }
                $this->plugin->players = [];
                $this->plugin->loadArena(true);
                $this->reloadTimer();
            }
        }
    }

    public function reloadTimer()
    {
        $this->startTime = 4;
        $this->gameTime = 10 * 60;
    }
}
