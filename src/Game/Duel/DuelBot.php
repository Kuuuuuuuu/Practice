<?php

declare(strict_types=1);

namespace Nayuki\Game\Duel;

use Nayuki\Entities\PracticeBot;
use Nayuki\Misc\AbstractListener;
use Nayuki\PracticeConfig;
use Nayuki\PracticeCore;
use pocketmine\block\utils\DyeColor;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use pocketmine\world\WorldException;

final class DuelBot extends AbstractListener
{
    /** @var string */
    public string $name;
    /** @var int */
    public int $status = DuelStatus::STARTING;
    /** @var Player */
    public Player $player1;
    /** @var PracticeBot|null */
    public ?PracticeBot $player2;
    /**  @var int */
    public int $time = DuelConfig::DEFAULT_TIME;
    /** @var int */
    private int $startSec = DuelConfig::DEFAULT_START_SEC;
    /** @var int */
    private int $endSec = DuelConfig::DEFAULT_END_SEC;
    /** @var World */
    private World $world;
    /** @var string[] */
    private array $spectators = [];

    public function __construct(string $name, Player $player1)
    {
        parent::__construct();
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
     * @param EntityDamageByEntityEvent $event
     * @return void
     * @priority HIGH
     */
    public function onEntityDamageEvent(EntityDamageByEntityEvent $event): void
    {
        $entity = $event->getEntity();
        $damager = $event->getDamager();
        if ($entity->getWorld()->getId() === $this->world->getId()) {
            if ($entity instanceof Player && $damager instanceof PracticeBot) {
                if ($event->getFinalDamage() >= $entity->getHealth()) {
                    $event->cancel();
                    $ESession = PracticeCore::getSessionManager()->getSession($entity);
                    $ESession->isDueling = false;
                }
            } elseif ($entity instanceof PracticeBot && $damager instanceof Player) {
                $PSession = PracticeCore::getSessionManager()->getSession($damager);
                if (!$PSession->isDueling) {
                    $event->cancel();
                }
            }
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
     * @param PlayerItemUseEvent $event
     * @return void
     * @priority HIGH
     * @handleCancelled true
     */
    public function onPlayerItemUseEvent(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if ($player->isSpectator() && in_array($player->getName(), $this->spectators) && $player->getWorld()->getId() === $this->world->getId() && $item->getCustomName() === TextFormat::RESET . TextFormat::RED . 'Leave Spectate') {
            $event->uncancel();
            $this->removeSpectator($player);
        }
    }

    /**
     * @return void
     */
    public function update(): void
    {
        $player = $this->player1;
        $session = PracticeCore::getSessionManager()->getSession($player);
        if (!$player->isOnline() || !$session->isDueling || ($this->player2 instanceof PracticeBot && (!$this->player2->isAlive() || $this->player2->isClosed()))) {
            $this->status = DuelStatus::ENDING;
        }
        switch ($this->status) {
            case DuelStatus::INGAME:
                $this->time--;
                if ($this->time <= 0) {
                    $this->status = DuelStatus::ENDING;
                }
                break;
            case DuelStatus::STARTING:
                if ($this->startSec >= 0 && $player->isOnline()) {
                    $player->sendTitle(PracticeConfig::COLOR . 'Starting in ' . $this->startSec, '', 1, 3, 1);
                    PracticeCore::getUtils()->playSound('random.click', $player);
                    if ($this->startSec === 3) {
                        $player->setNoClientPredictions();
                        $player->setGamemode(GameMode::SURVIVAL);
                        $player->teleport(new Location(24, 101, 40, $this->world, 190, 0));
                    } elseif ($this->startSec === 0) {
                        $player->setNoClientPredictions();
                        $player->sendTitle(PracticeConfig::COLOR . 'Fight!', '', 1, 5, 1);
                        PracticeCore::getUtils()->playSound('random.anvil_use', $player);
                        $this->player2 = new PracticeBot(new Location(24, 101, 10, Server::getInstance()->getWorldManager()->getWorldByName($this->world->getFolderName()), 0, 0), $this->player1->getSkin(), null, $this->player1->getName());
                        $this->player2->spawnToAll();
                        $this->status = DuelStatus::INGAME;
                    }
                    $this->startSec--;
                }
                break;
            case DuelStatus::ENDING:
                $this->endSec--;
                if ($this->endSec <= 0) {
                    $this->onEnd(($this->player2 instanceof PracticeBot && ($this->player2->isAlive() || !$this->player2->isClosed())) ? $this->player2 : $player);
                }
                break;
        }
    }

    /**
     * @param Player|PracticeBot $playerLeft
     * @return void
     */
    public function onEnd(Player|PracticeBot $playerLeft): void
    {
        $player = $this->player1;
        $utils = PracticeCore::getUtils();
        $winnerName = $playerLeft instanceof Player ? $player->getName() : 'PracticeBot';
        $loserName = $winnerName === $player->getName() ? 'PracticeBot' : $player->getName();
        $winnerMessage = TextFormat::GREEN . 'Winner: ' . TextFormat::WHITE . $winnerName;
        $loserMessage = TextFormat::RED . 'Loser: ' . TextFormat::WHITE . $loserName;

        $sendEndMessages = function (Player $recipient) use ($winnerMessage, $loserMessage) {
            $separator = TextFormat::WHITE . '-----------------------';
            $recipient->sendMessage($separator);
            $recipient->sendMessage($winnerMessage);
            $recipient->sendMessage($loserMessage);
            $recipient->sendMessage($separator);
        };

        $spectatorNames = [];
        foreach ($this->spectators as $spectatorName) {
            $spectator = Server::getInstance()->getPlayerExact($spectatorName);
            if ($spectator !== null && $spectator->isOnline()) {
                $sendEndMessages($spectator);
                $spectatorNames[] = $spectator->getDisplayName();
                $utils->teleportToLobby($spectator);
            }
        }

        $spectatorsCount = count($spectatorNames);
        $loopedSpectatorsSize = (int)ceil($spectatorsCount / 3);
        $visibleSpectators = array_slice($spectatorNames, 0, $loopedSpectatorsSize);
        $hiddenSpectatorsCount = $spectatorsCount - $loopedSpectatorsSize;
        $spectatorsMessage = TextFormat::GRAY . 'Spectators: ';

        if ($spectatorsCount === 0) {
            $spectatorsMessage .= TextFormat::WHITE . 'None';
        } else {
            $spectatorsMessage .= TextFormat::WHITE . implode(TextFormat::DARK_GRAY . ', ', $visibleSpectators);
            if ($hiddenSpectatorsCount > 0) {
                $spectatorsMessage .= TextFormat::DARK_GRAY . ', ' . TextFormat::WHITE . "(+$hiddenSpectatorsCount more)";
            }
        }

        if ($player->isOnline()) {
            $sendEndMessages($player);
            $player->sendMessage($spectatorsMessage);
            $utils->teleportToLobby($player);
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
