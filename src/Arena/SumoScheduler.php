<?php

declare(strict_types=1);

namespace Kuu\Arena;

use Exception;
use Kuu\Loader;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class SumoScheduler extends Task
{
    private int $tick = 0;
    private SumoHandler $plugin;
    private int $startTime = 4;
    private int|float $gameTime = 5 * 30;

    public function __construct(SumoHandler $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @throws Exception
     */
    public function onRun(): void
    {
        if (!$this->plugin->setup) {
            $this->tick++;
            if (($this->tick % 20 === 0) && $this->plugin->phase !== SumoHandler::PHASE_RESTART) {
                foreach ($this->plugin->players as $player) {
                    if ($this->plugin->inGame($player) && $player->getWorld() !== $this->plugin->level) {
                        $this->plugin->disconnectPlayer($player);
                    }
                }
            }
            switch ($this->plugin->phase) {
                case SumoHandler::PHASE_LOBBY:
                    {
                        if ($this->tick % 20 === 0) {
                            if (count($this->plugin->players) >= 2) {
                                if ($this->startTime >= 0) {
                                    $this->startTime--;
                                    foreach ($this->plugin->players as $player) {
                                        /** @var $player Player */
                                        $player->sendTitle('§d' . $this->startTime, '', 1, 3, 1);
                                        Loader::getArenaUtils()->playSound('random.click', $player);
                                    }
                                }
                                if ($this->startTime === 0) {
                                    $this->plugin->startGame();
                                    foreach ($this->plugin->players as $player) {
                                        Loader::getArenaUtils()->playSound('random.anvil_use', $player);
                                    }
                                }
                            } else {
                                $this->startTime = 4;
                            }
                        }
                    }
                    break;
                case SumoHandler::PHASE_GAME:
                    {
                        if ($this->tick % 20 === 0) {
                            if ($this->gameTime > 0) {
                                $this->gameTime--;
                            } else {
                                $this->plugin->startRestart();
                            }
                        }
                        if ($this->plugin->checkEnd()) {
                            $this->plugin->startRestart();
                        }
                        if ($this->tick % 5 === 0) {
                            foreach ($this->plugin->players as $player) {
                                /* @var Player $player */
                                if ($player->getWorld() !== $this->plugin->level) {
                                    $this->plugin->disconnectPlayer($player);
                                    Loader::getArenaUtils()->addDeath($player);
                                    $player->sendMessage(Loader::getPrefixCore() . '§cYou lost Elo ' . Loader::getArenaUtils()->getData($player->getName())->removeElo() . ' Elos!');
                                } elseif ($player->getPosition()->getY() <= 50) {
                                    $this->plugin->disconnectPlayer($player);
                                    Loader::getArenaUtils()->addDeath($player);
                                    Loader::getArenaUtils()->getData($player->getName())->removeElo();
                                    $player->sendMessage(Loader::getPrefixCore() . '§cYou lost Elo ' . Loader::getArenaUtils()->getData($player->getName())->removeElo() . ' Elos!');
                                }
                                if ($player->isImmobile()) {
                                    $player->setImmobile(false);
                                }
                            }
                        }
                    }
                    break;
                case SumoHandler::PHASE_RESTART:
                    {
                        if ($this->tick % 5 === 0) {
                            foreach ($this->plugin->players as $player) {
                                $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()?->getSafeSpawn());
                                $player->getInventory()->clearAll();
                                $player->getArmorInventory()->clearAll();
                                $player->getEffects()->clear();
                                Loader::getArenaUtils()->GiveLobbyItem($player);
                                Loader::getArenaUtils()->addKill($player);
                                $player->setGamemode(GameMode::ADVENTURE());
                            }
                            $this->plugin->players = [];
                            $this->plugin->loadArena(true);
                            $this->reloadTimer();
                        }
                    }
                    break;
            }
        }
    }

    public function reloadTimer(): void
    {
        $this->startTime = 4;
        $this->gameTime = 10 * 60;
    }
}