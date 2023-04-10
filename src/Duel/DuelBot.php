<?php

namespace Nayuki\Duel;

use Nayuki\Entities\PracticeBot;
use Nayuki\PracticeCore;
use pocketmine\entity\Location;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;
use pocketmine\world\WorldException;

final class DuelBot
{
    private static int $status = DuelStatus::STARTING;
    /** @var string */
    public string $name;
    /** @var int */
    private int $time = 200;
    /** @var int */
    private int $startSec = 3;
    /** @var int */
    private int $endSec = 5;
    /** @var Player */
    private Player $player1;
    /** @var PracticeBot|null */
    private ?PracticeBot $player2;
    /** @var World */
    private World $world;

    public function __construct(string $name, Player $player1)
    {
        $world = Server::getInstance()->getWorldManager()->getWorldByName($name);
        if ($world === null) {
            throw new WorldException('World does not exist');
        }
        $session = PracticeCore::getSessionManager()->getSession($player1);
        $session->DuelClass = $this;
        $this->world = $world;
        $this->player1 = $player1;
        $this->player2 = null;
        $this->name = $name;
    }

    /**
     * @param int $tick
     * @return void
     */
    public function update(int $tick): void
    {
        $player = $this->player1;
        $session = PracticeCore::getSessionManager()->getSession($player);
        if (!$player->isOnline() || !$session->isDueling || ($this->player2 instanceof PracticeBot && (!$this->player2->isAlive() || $this->player2->isClosed()))) {
            self::$status = DuelStatus::ENDING;
        }
        if ($tick % 20 === 0) {
            switch (self::$status) {
                case DuelStatus::INGAME:
                    $this->time--;
                    if ($this->time <= 0) {
                        self::$status = DuelStatus::ENDING;
                    }
                    break;
                case DuelStatus::STARTING:
                    if ($this->startSec >= 0) {
                        $player->sendTitle('§bStarting in ' . $this->startSec, '', 1, 3, 1);
                        PracticeCore::getUtils()->playSound('random.click', $player);
                        if ($this->startSec === 3) {
                            if ($player->isOnline()) {
                                $player->setImmobile();
                                $player->setGamemode(GameMode::ADVENTURE());
                                $player->teleport(new Location(24, 101, 40, $this->world, 190, 0));
                            }
                        } elseif ($this->startSec === 0) {
                            $player->setImmobile(false);
                            $player->sendTitle('§bFight!', '', 1, 5, 1);
                            PracticeCore::getUtils()->playSound('random.anvil_use', $player);
                            $this->player2 = new PracticeBot(new Location(24, 101, 10, Server::getInstance()->getWorldManager()->getWorldByName($this->world->getFolderName()), 0, 0), $this->player1->getSkin(), null, $this->player1->getName());
                            $this->player2->spawnToAll();
                            self::$status = DuelStatus::INGAME;
                        }
                        $this->startSec--;
                    }
                    break;
                case DuelStatus::ENDING:
                    $this->endSec--;
                    if ($this->endSec <= 0) {
                        $this->onEnd();
                    }
                    break;
            }
        }
    }

    public function onEnd(?Player $playerLeft = null): void
    {
        $player = $this->player1;
        $spawn = Server::getInstance()->getWorldManager()->getDefaultWorld();
        $winnerName = $playerLeft instanceof Player ? $player->getName() : 'PracticeBot';
        $loserName = (!$playerLeft instanceof Player) ? $player->getName() : 'PracticeBot';
        $winnerMessage = "§aWinner: §f$winnerName";
        $loserMessage = "§cLoser: §f$loserName";
        if ($player->isOnline()) {
            $session = PracticeCore::getSessionManager()->getSession($player);
            $player->sendMessage('§f-----------------------');
            $player->sendMessage($winnerMessage);
            $player->sendMessage($loserMessage);
            $player->sendMessage('§f-----------------------');
            $session->isDueling = false;
            $session->DuelKit = null;
            $session->BoxingPoint = 0;
            $session->DuelClass = null;
            $session->setOpponent(null);
            $session->isCombat = false;
            $session->CombatTime = 0;
            $session->isQueueing = false;
            if ($spawn instanceof World) {
                $player->teleport($spawn->getSafeSpawn(), 0, 0);
            }
            PracticeCore::getUtils()->setLobbyItem($player);
            PracticeCore::getScoreboardManager()->setLobbyScoreboard($player);
            $player->setHealth(20);
        }
        PracticeCore::getDuelManager()->stopMatch($this->name);
    }

    /**
     * @return int
     */
    public function getSeconds(): int
    {
        return $this->time;
    }
}
