<?php

declare(strict_types=1);

namespace Kohaku\Core\Arena;

use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ArenaUtils;
use pocketmine\player\Player;
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
                if (count($this->plugin->players) >= 2) {
                    if ($this->startTime > 0) {
                        $this->startTime--;
                        foreach ($this->plugin->players as $player) {
                            $player->sendMessage(Loader::getPrefixCore() . "§cThe game will start in §e" . $this->startTime . "§c seconds!");
                        }
                    }
                    if ($this->startTime == 0) {
                        $this->plugin->startGame();
                    }
                } else {
                    $this->startTime = 4;
                    $this->gameTime = 5 * 30;
                }
            } else if ($this->plugin->phase === SumoHandler::PHASE_GAME) {
                if ($this->gameTime > 0) {
                    $this->gameTime--;
                } else if ($this->gameTime < 0) {
                    $this->plugin->startRestart();
                }
                if ($this->plugin->checkEnd()) {
                    $this->plugin->startRestart();
                }
                foreach ($this->plugin->players as $player) {
                    /** @var $player Player */
                    if ($player->isImmobile()) {
                        $player->setImmobile(false);
                    }
                    if ($this->plugin->inGame($player)) {
                        if ($player->getPosition()->getY() <= 30) {
                            $this->plugin->disconnectPlayer($player);
                        } else if ($player->getWorld() !== $this->plugin->level) {
                            $this->plugin->disconnectPlayer($player);
                        }
                    }
                }
            } else if ($this->plugin->phase === SumoHandler::PHASE_RESTART) {
                foreach ($this->plugin->players as $player) {
                    $player->teleport($this->plugin->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                    $player->getInventory()->clearAll();
                    $player->getArmorInventory()->clearAll();
                    $player->getEffects()->clear();
                    ArenaUtils::getInstance()->GiveItem($player);
                    ArenaUtils::getInstance()->addKill($player);
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
