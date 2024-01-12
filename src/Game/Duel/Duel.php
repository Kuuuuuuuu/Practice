<?php

declare(strict_types=1);

namespace Nayuki\Game\Duel;

use Nayuki\Game\Kits\Boxing;
use Nayuki\Game\Kits\Kit;
use Nayuki\Misc\AbstractListener;
use Nayuki\PracticeConfig;
use Nayuki\PracticeCore;
use pocketmine\block\utils\DyeColor;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use pocketmine\world\WorldException;

final class Duel extends AbstractListener
{
    /** @var int */
    public int $status = DuelStatus::STARTING;
    /** @var string */
    public string $name;
    /** @var Player */
    public Player $player1;
    /** @var Player */
    public Player $player2;
    /** @var int */
    public int $time = DuelConfig::DEFAULT_TIME;
    /** @var Kit */
    public Kit $kit;
    /** @var int */
    private int $startSec = DuelConfig::DEFAULT_START_SEC;
    /** @var int */
    private int $endSec = DuelConfig::DEFAULT_END_SEC;
    /** @var World */
    private World $world;
    /** @var Player|null */
    private ?Player $winner = null;
    /** @var Player|null */
    private ?Player $loser = null;
    /** @var string[] */
    private array $spectators = [];

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

        if ($this->status !== DuelStatus::INGAME) {
            $event->cancel();
            return;
        }

        if (($damager instanceof Player && $player instanceof Player) && ($this->kit instanceof Boxing) && ($damager->getWorld()->getId() === $this->world->getId() && $player->getWorld()->getId() === $this->world->getId())) {
            $Dsession = PracticeCore::getSessionManager()->getSession($damager);
            if ($Dsession->BoxingPoint === 100) {
                $PSession = PracticeCore::getSessionManager()->getSession($player);
                $PSession->isDueling = false;
            }
            $Dsession->BoxingPoint++;
        }
    }

    /**
     * @param PlayerQuitEvent $event
     * @return void
     * @priority HIGH
     */
    public function onPlayerQuitEvent(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        if ($player->getWorld()->getId() === $this->world->getId() && in_array($player->getName(), $this->spectators)) {
            $this->removeSpectator($player);
        }
    }

    /**
     * @param Player $player
     * @return void
     */
    public function removeSpectator(Player $player): void
    {
        $this->spectators = array_diff($this->spectators, [$player->getName()]);
        PracticeCore::getUtils()->teleportToLobby($player);
        foreach ($this->world->getPlayers() as $players) {
            $players->sendMessage(PracticeCore::getPrefixCore() . $player->getName() . TextFormat::RED . ' has not longer spectating');
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
     * @param EntityDamageEvent $event
     * @return void
     * @priority HIGH
     */
    public function onEntityDamageEvent(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        $cause = $event->getCause();
        if ($entity instanceof Player && $entity->getWorld()->getId() === $this->world->getId()) {
            if ($cause == EntityDamageEvent::CAUSE_VOID) {
                $event->cancel();
                $ESession = PracticeCore::getSessionManager()->getSession($entity);
                $ESession->isDueling = false;
                $winner = ($entity->getName() !== $this->player1->getName()) ? $this->player1 : $this->player2;
                $entity->teleport($winner->getPosition());
            }
        }
    }

    /**
     * @param PlayerItemUseEvent $event
     * @return void
     * @priority HIGH
     * @handleCancelled true
     */
    public function onPlayerItemUseEvent(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if ($player->isSpectator() && $player->getWorld()->getId() === $this->world->getId() && in_array($player->getName(), $this->spectators) && $item->getCustomName() === TextFormat::RESET . TextFormat::RED . 'Leave Spectate') {
            $event->uncancel();
            $this->removeSpectator($player);
        }
    }

    /**
     * @return void
     */
    public function update(): void
    {
        $players = $this->getPlayers();

        if ($this->status !== DuelStatus::ENDING) {
            foreach ($players as $player) {
                $session = PracticeCore::getSessionManager()->getSession($player);
                if (!$session->isDueling || !$player->isOnline()) {
                    $this->loser = $player;
                    $this->winner = ($player->getName() !== $this->player1->getName()) ? $this->player1 : $this->player2;
                    $this->onEnd();
                    return;
                }
            }
        }

        switch ($this->status) {
            case DuelStatus::INGAME:
                $this->time--;
                if ($this->time <= 0) {
                    $this->status = DuelStatus::ENDING;
                }
                break;
            case DuelStatus::STARTING:
                if ($this->startSec >= 0) {
                    foreach ($players as $player) {
                        if ($player->isOnline()) {
                            $player->sendTitle(PracticeConfig::COLOR . 'Starting in ' . $this->startSec, '', 1, 3, 1);
                            PracticeCore::getUtils()->playSound('random.click', $player);
                        }
                    }

                    if ($this->startSec === 3) {
                        $kitName = $this->kit->getName();
                        $player1Location = ($kitName === 'Sumo') ? new Location(8, 101, 2, $this->world, 0, 0) : new Location(24, 101, 40, $this->world, 180, 0);
                        $player2Location = ($kitName === 'Sumo') ? new Location(8, 101, 14, $this->world, 180, 0) : new Location(24, 101, 10, $this->world, 0, 0);

                        $this->player1->teleport($player1Location);
                        $this->player2->teleport($player2Location);

                        foreach ($players as $player) {
                            if ($player->isOnline()) {
                                $player->setNoClientPredictions();
                                $player->setGamemode(GameMode::SURVIVAL);
                                $this->kit->setEffect($player);
                                $player->getArmorInventory()->setContents($this->kit->getArmorItems());
                                $player->getInventory()->setContents($this->kit->getInventoryItems());
                            }
                        }
                    } elseif ($this->startSec === 0) {
                        foreach ($players as $player) {
                            if ($player->isOnline()) {
                                $player->setNoClientPredictions(false);
                                $player->sendTitle(PracticeConfig::COLOR . 'Fight!', '', 1, 5, 1);
                                PracticeCore::getUtils()->playSound('random.anvil_use', $player);
                            }
                        }

                        $this->status = DuelStatus::INGAME;
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

    /**
     * @return void
     */
    public function onEnd(): void
    {
        $sessionManager = PracticeCore::getSessionManager();
        $utils = PracticeCore::getUtils();
        $winner = $this->winner;
        $loser = $this->loser;

        $sendEndMessages = function (Player $recipient, $separator, $winnerMessage, $loserMessage) use ($utils) {
            $recipient->sendMessage($separator);
            $recipient->sendMessage($winnerMessage);
            $recipient->sendMessage($loserMessage);
            $recipient->sendMessage($separator);
            $utils->teleportToLobby($recipient);
        };

        $winnerName = $winner !== null ? $winner->getName() : 'None';
        $loserName = $loser !== null ? $loser->getName() : 'None';
        $separator = TextFormat::WHITE . '-----------------------';
        $winnerMessage = TextFormat::GREEN . 'Winner: ' . TextFormat::WHITE . $winnerName;
        $loserMessage = TextFormat::RED . 'Loser: ' . TextFormat::WHITE . $loserName;
        $spectatorsCount = count($this->spectators);
        $spectatorsMessage = TextFormat::GRAY . 'Spectators: ';

        if ($winner !== null && $winner->isOnline()) {
            $WinnerSession = $sessionManager->getSession($winner);
            $WinnerSession->kills++;
            $WinnerSession->killStreak++;
        }

        if ($loser !== null && $loser->isOnline()) {
            $LoserSession = $sessionManager->getSession($loser);
            $LoserSession->deaths++;
            $LoserSession->killStreak = 0;
        }

        if ($spectatorsCount === 0) {
            $spectatorsMessage .= TextFormat::WHITE . 'None';
        } else {
            $spectatorNames = [];
            foreach ($this->spectators as $spectatorName) {
                $spectator = Server::getInstance()->getPlayerExact($spectatorName);
                if ($spectator !== null && $spectator->isOnline()) {
                    $sendEndMessages($spectator, $separator, $winnerMessage, $loserMessage);
                    $spectatorNames[] = $spectator->getDisplayName();
                }
            }

            $loopedSpectatorsSize = (int)ceil($spectatorsCount / 3);
            $visibleSpectators = array_slice($spectatorNames, 0, $loopedSpectatorsSize);
            $hiddenSpectatorsCount = $spectatorsCount - $loopedSpectatorsSize;
            $spectatorsMessage .= TextFormat::WHITE . implode(TextFormat::DARK_GRAY . ', ', $visibleSpectators);

            if ($hiddenSpectatorsCount > 0) {
                $spectatorsMessage .= TextFormat::DARK_GRAY . ', ' . TextFormat::WHITE . "(+$hiddenSpectatorsCount more)";
            }
        }

        foreach ($this->getPlayers() as $online) {
            if ($online instanceof Player && $online->isOnline()) {
                $sendEndMessages($online, $separator, $winnerMessage, $loserMessage);
                $online->sendMessage($spectatorsMessage);
            }
        }

        PracticeCore::getDuelManager()->stopMatch($this->name);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function addSpectator(Player $player): void
    {
        $this->spectators[] = $player->getName();
        $session = PracticeCore::getSessionManager()->getSession($player);
        $session->spectating = true;
        $session->spectatingDuel = $this;
        $item = VanillaItems::DYE()->setColor(DyeColor::RED)->setCustomName(TextFormat::RESET . TextFormat::RED . 'Leave Spectate');
        $player->setGamemode(GameMode::SPECTATOR);
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setOnFire(0);
        $player->setHealth(20);
        $player->setScale(1);
        $player->getInventory()->setItem(8, $item);
        $player->teleport($this->player1->getLocation());

        foreach ($this->world->getPlayers() as $players) {
            $players->sendMessage(PracticeCore::getPrefixCore() . TextFormat::WHITE . $player->getName() . TextFormat::GREEN . ' is now spectating the duel.');
        }
    }

    /**
     * @return int
     */
    public function getSeconds(): int
    {
        return $this->time;
    }
}
