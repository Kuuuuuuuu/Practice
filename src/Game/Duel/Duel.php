<?php

namespace Nayuki\Game\Duel;

use Nayuki\Game\Kits\Kit;
use Nayuki\Misc\AbstractListener;
use Nayuki\PracticeCore;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;
use pocketmine\world\WorldException;

final class Duel extends AbstractListener
{
    /** @var string */
    public string $name;
    /** @var int */
    private int $time = 300;
    /** @var int */
    private int $startSec = 3;
    /** @var Player */
    private Player $player1;
    /** @var Player */
    private Player $player2;
    /** @var World */
    private World $world;
    /** @var Player|null */
    private ?Player $winner = null;
    /** @var Player|null */
    private ?Player $loser = null;
    /** @var Kit */
    private Kit $kit;
    /** @var bool */
    private bool $ended = false;

    public function __construct(string $name, Player $player1, Player $player2, Kit $kit)
    {
        parent::__construct();
        $world = Server::getInstance()->getWorldManager()->getWorldByName($name);
        if ($world === null) {
            throw new WorldException('World does not exist');
        }
        $this->world = $world;
        $this->kit = $kit;
        $this->name = $name;
        $this->player1 = $player1;
        $this->player2 = $player2;
        foreach ([$player1, $player2] as $players) {
            $session = PracticeCore::getSessionManager()::getSession($players);
            $session->DuelClass = $this;
        }
    }

    /**
     * @param EntityDamageByEntityEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onEntityDamageByEntityEvent(EntityDamageByEntityEvent $event): void
    {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        if ($damager instanceof Player && $player instanceof Player && $this->kit->getName() === 'Boxing' && $damager->getWorld() === $this->world) {
            $session = PracticeCore::getSessionManager()::getSession($damager);
            $session->BoxingPoint++;
            if ($session->BoxingPoint > 99) {
                $player->kill();
            }
        }
    }

    /**
     * @param int $tick
     * @return void
     */
    public function update(int $tick): void
    {
        if ($tick % 5 === 0) {
            foreach ($this->getPlayers() as $player) {
                $session = PracticeCore::getSessionManager()::getSession($player);
                if ($player->isOnline()) {
                    if ($session->isDueling) {
                        if ($player->getWorld() === $this->world && ($player->getPosition()->getY() < 98)) {
                            $player->kill();
                        }
                    } else {
                        $this->loser = $player;
                        $this->winner = $player->getName() !== $this->player1->getName() ? $this->player1 : $this->player2;
                        $this->onEnd();
                    }
                } else {
                    $this->loser = $player;
                    $this->winner = $player->getName() !== $this->player1->getName() ? $this->player1 : $this->player2;
                    $this->onEnd($player);
                }
            }
        }
        if ($tick % 20 === 0) {
            if ($this->startSec >= 0) {
                if ($this->startSec > 0) {
                    foreach ($this->getPlayers() as $player) {
                        $player->sendTitle('§bStarting in ' . $this->startSec, '', 1, 3, 1);
                        PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $player);
                    }
                }
                if ($this->startSec === 3) {
                    if ($this->kit->getName() === 'Sumo') {
                        $this->player1->teleport(new Location(8, 101, 2, $this->world, 0, 0));
                        $this->player2->teleport(new Location(8, 101, 14, $this->world, 180, 0));
                    } else {
                        $this->player1->teleport(new Location(24, 101, 40, $this->world, 180, 0));
                        $this->player2->teleport(new Location(24, 101, 10, $this->world, 0, 0));
                    }
                    foreach ($this->getPlayers() as $player) {
                        $player->setGamemode(GameMode::ADVENTURE());
                        $this->kit->setEffect($player);
                        $player->getArmorInventory()->setContents($this->kit->getArmorItems());
                        $player->getInventory()->setContents($this->kit->getInventoryItems());
                        $player->setImmobile();
                    }
                } elseif ($this->startSec === 0) {
                    foreach ($this->getPlayers() as $player) {
                        $player->setImmobile(false);
                        $player->sendTitle('§bFight!', '', 1, 5, 1);
                        PracticeCore::getInstance()->getPracticeUtils()->playSound('random.levelup', $player);
                    }
                }
                $this->startSec--;
            } else {
                if ($this->time <= 0) {
                    $this->onEnd();
                }
                $this->time--;
            }
        }
    }

    /**
     * @return array<Player>
     */
    private function getPlayers(): array
    {
        return [$this->player1, $this->player2];
    }

    /**
     * @param Player|null $playerLeft
     * @return void
     */
    public function onEnd(?Player $playerLeft = null): void
    {
        if (!$this->ended) {
            foreach ($this->getPlayers() as $online) {
                if ($playerLeft === null || $online->getName() !== $playerLeft->getName()) {
                    if ($online instanceof Player) {
                        $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
                        $session = PracticeCore::getSessionManager()::getSession($online);
                        $online->sendMessage('§f-----------------------');
                        $winnerMessage = '§aWinner: §f';
                        $winnerMessage .= $this->winner !== null ? $this->winner->getName() : 'None';
                        $online->sendMessage($winnerMessage);
                        $loserMessage = '§cLoser: §f';
                        $loserMessage .= $this->loser !== null ? $this->loser->getName() : 'None';
                        $online->sendMessage($loserMessage);
                        $online->sendMessage('§f-----------------------');
                        PracticeCore::getPracticeUtils()->setLobbyItem($online);
                        PracticeCore::getScoreboardManager()->setLobbyScoreboard($online);
                        $session->isDueling = false;
                        $session->DuelKit = null;
                        $session->BoxingPoint = 0;
                        $session->DuelClass = null;
                        $session->setOpponent(null);
                        if ($this->winner !== null) {
                            $WinnerSession = PracticeCore::getSessionManager()::getSession($this->winner);
                            $WinnerSession->kills++;
                            $WinnerSession->killStreak++;
                        }
                        if ($this->loser !== null) {
                            $LoserSession = PracticeCore::getSessionManager()::getSession($this->loser);
                            $LoserSession->deaths++;
                            $LoserSession->killStreak = 0;
                        }
                        $online->setHealth(20);
                        $online->setImmobile(false);
                        if ($world instanceof World) {
                            $online->teleport($world->getSafeSpawn(), 0, 0);
                        }
                    }
                }
            }
            $this->ended = true;
        }
        PracticeCore::getDuelManager()->stopMatch($this->world->getFolderName());
    }

    /**
     * @return int
     */
    public function getSeconds(): int
    {
        return $this->time;
    }
}
