<?php

namespace Nayuki\Duel;

use Nayuki\Game\Kits\Boxing;
use Nayuki\Game\Kits\Kit;
use Nayuki\Misc\AbstractListener;
use Nayuki\PracticeCore;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;
use pocketmine\world\WorldException;

final class Duel extends AbstractListener
{
    /** @var int */
    public static int $status = DuelStatus::STARTING;
    /** @var string */
    public string $name;
    /** @var int */
    private int $time = 600;
    /** @var int */
    private int $startSec = 3;
    /** @var int */
    private int $endSec = 5;
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
            $session = PracticeCore::getSessionManager()->getSession($players);
            $session->DuelClass = $this;
        }
    }

    /**
     * @param EntityDamageByEntityEvent $event
     * @return void
     * @priority HIGH
     */
    public function onEntityDamageByEntityEvent(EntityDamageByEntityEvent $event): void
    {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        if (self::$status !== DuelStatus::INGAME) {
            $event->cancel();
            return;
        }
        if (($damager instanceof Player && $player instanceof Player) && ($this->kit instanceof Boxing) && ($damager->getWorld() === $this->world && $player->getWorld() === $this->world)) {
            $Dsession = PracticeCore::getSessionManager()->getSession($damager);
            $Dsession->BoxingPoint++;
            if ($Dsession->BoxingPoint > 99) {
                $PSession = PracticeCore::getSessionManager()->getSession($player);
                $PSession->isDueling = false;
            }
        }
    }

    /**
     * @param PlayerMoveEvent $event
     * @return void
     * @priority HIGH
     */
    public function onPlayerMoveEvent(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        if ($player->getWorld() === $this->world) {
            $session = PracticeCore::getSessionManager()->getSession($player);
            if ($session->isDueling && self::$status === DuelStatus::STARTING) {
                $event->cancel();
            }
        }
    }

    /**
     * @param EntityDamageEvent $event
     * @return void
     * @priority HIGH
     */
    public function onEntityDamageEvent(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        $cause = $event->getCause();
        if ($entity instanceof Player && $cause === EntityDamageEvent::CAUSE_VOID) {
            $event->cancel();
            $ESession = PracticeCore::getSessionManager()->getSession($entity);
            $ESession->isDueling = false;
            $winner = ($entity->getName() !== $this->player1->getName()) ? $this->player1 : $this->player2;
            $entity->teleport($winner->getPosition());
        }
    }

    /**
     * @param int $tick
     * @return void
     */
    public function update(int $tick): void
    {
        $players = $this->getPlayers();
        if ($tick % 5 === 0 && self::$status !== DuelStatus::ENDING) {
            foreach ($players as $player) {
                $session = PracticeCore::getSessionManager()->getSession($player);
                if (!$session->isDueling || !$player->isOnline()) {
                    $this->loser = $player;
                    $this->winner = ($player->getName() !== $this->player1->getName()) ? $this->player1 : $this->player2;
                    $this->onEnd($player);
                    break;
                }
            }
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
                        foreach ($players as $player) {
                            $player->sendTitle('§bStarting in ' . $this->startSec, '', 1, 3, 1);
                            PracticeCore::getUtils()->playSound('random.click', $player);
                        }
                        if ($this->startSec === 3) {
                            $kitName = $this->kit->getName();
                            $player1Location = ($kitName === 'Sumo') ? new Location(8, 101, 2, $this->world, 0, 0) : new Location(24, 101, 40, $this->world, 180, 0);
                            $player2Location = ($kitName === 'Sumo') ? new Location(8, 101, 14, $this->world, 180, 0) : new Location(24, 101, 10, $this->world, 0, 0);
                            $this->player1->teleport($player1Location);
                            $this->player2->teleport($player2Location);
                            foreach ($players as $player) {
                                $player->setGamemode(GameMode::ADVENTURE());
                                $this->kit->setEffect($player);
                                $player->getArmorInventory()->setContents($this->kit->getArmorItems());
                                $player->getInventory()->setContents($this->kit->getInventoryItems());
                            }
                        } elseif ($this->startSec === 0) {
                            foreach ($players as $player) {
                                $player->sendTitle('§bFight!', '', 1, 5, 1);
                                PracticeCore::getUtils()->playSound('random.anvil_use', $player);
                            }
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

    /**
     * @return Player[]
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
        foreach ($this->getPlayers() as $online) {
            if ($playerLeft === null || $online->getName() !== $playerLeft->getName()) {
                if ($online instanceof Player) {
                    $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
                    $session = PracticeCore::getSessionManager()->getSession($online);
                    $online->sendMessage('§f-----------------------');
                    $winnerMessage = '§aWinner: §f';
                    $winnerMessage .= $this->winner !== null ? $this->winner->getName() : 'None';
                    $online->sendMessage($winnerMessage);
                    $loserMessage = '§cLoser: §f';
                    $loserMessage .= $this->loser !== null ? $this->loser->getName() : 'None';
                    $online->sendMessage($loserMessage);
                    $online->sendMessage('§f-----------------------');
                    PracticeCore::getUtils()->setLobbyItem($online);
                    PracticeCore::getScoreboardManager()->setLobbyScoreboard($online);
                    $session->isDueling = false;
                    $session->DuelKit = null;
                    $session->BoxingPoint = 0;
                    $session->DuelClass = null;
                    $session->setOpponent(null);
                    $session->isCombat = false;
                    $session->CombatTime = 0;
                    $session->isQueueing = false;
                    if ($this->winner !== null) {
                        $WinnerSession = PracticeCore::getSessionManager()->getSession($this->winner);
                        $WinnerSession->kills++;
                        $WinnerSession->killStreak++;
                    }
                    if ($this->loser !== null) {
                        $LoserSession = PracticeCore::getSessionManager()->getSession($this->loser);
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
