<?php

declare(strict_types=1);

namespace Kohaku\Core\Arena;

use Exception;
use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ArenaUtils;
use Kohaku\Core\Utils\ScoreboardUtils;
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
            if ($this->tick % 20 === 0) {
                foreach ($this->plugin->players as $player) {
                    if ($this->plugin->inGame($player)) {
                        if ($player->getWorld() !== $this->plugin->level) {
                            $this->plugin->disconnectPlayer($player);
                        }
                    }
                }
                if ($this->plugin->phase === SumoHandler::PHASE_GAME) {
                    if ($this->gameTime > 0) {
                        $this->gameTime--;
                    } else {
                        $this->plugin->startRestart();
                    }
                } else if ($this->plugin->phase === SumoHandler::PHASE_LOBBY) {
                    if (count($this->plugin->players) >= 2) {
                        if ($this->startTime >= 0) {
                            $this->startTime--;
                            foreach ($this->plugin->players as $player) {
                                /** @var $player Player */
                                $player->sendTitle("§b" . $this->startTime, "", 1, 1, 1);
                                ArenaUtils::getInstance()->playSound("random.click", $player);
                            }
                        }
                        if ($this->startTime === 0) {
                            $this->plugin->startGame();
                            foreach ($this->plugin->players as $player) {
                                ArenaUtils::getInstance()->playSound("random.anvil_use", $player);
                            }
                        }
                    } else {
                        $this->startTime = 4;
                    }
                }
            }
            if ($this->tick % 5 === 0) {
                if ($this->plugin->phase === SumoHandler::PHASE_GAME) {
                    if ($this->plugin->checkEnd()) {
                        $this->plugin->startRestart();
                    }
                    foreach ($this->plugin->players as $player) {
                        /** @var $player Player */
                        if ($player->getWorld() !== $this->plugin->level) {
                            $this->plugin->disconnectPlayer($player);
                            $player->sendMessage(Loader::getPrefixCore() . "§cYou lost Elo " . ArenaUtils::getInstance()->getData($player->getName())->removeElo() . " Elos!");
                        } else if ($player->getPosition()->getY() <= 50) {
                            $this->plugin->disconnectPlayer($player);
                            ArenaUtils::getInstance()->getData($player->getName())->removeElo();
                            $player->sendMessage(Loader::getPrefixCore() . "§cYou lost Elo " . ArenaUtils::getInstance()->getData($player->getName())->removeElo() . " Elos!");
                        }
                        $player->setImmobile(false);
                    }
                } else if ($this->plugin->phase === SumoHandler::PHASE_RESTART) {
                    foreach ($this->plugin->players as $player) {
                        $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
                        $player->getInventory()->clearAll();
                        $player->getArmorInventory()->clearAll();
                        $player->getEffects()->clear();
                        ArenaUtils::getInstance()->GiveItem($player);
                        ArenaUtils::getInstance()->addKill($player);
                        ScoreboardUtils::getInstance()->sb($player);
                        $player->setGamemode(GameMode::ADVENTURE());
                    }
                    $this->plugin->players = [];
                    $this->plugin->loadArena(true);
                    $this->reloadTimer();
                }
            }
        }
    }

    public function reloadTimer()
    {
        $this->startTime = 4;
        $this->gameTime = 10 * 60;
    }
}