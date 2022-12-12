<?php

namespace Nayuki\Duel;

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

class Duel extends AbstractListener
{
    /** @var int */
    private int $time = 903;
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
        $this->player1 = $player1;
        $this->player2 = $player2;
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
            $session = PracticeCore::getPlayerSession()::getSession($damager);
            $session->BoxingPoint++;
            if ($session->BoxingPoint > 99) {
                $player->kill();
            }
            foreach ([$player, $damager] as $p) {
                PracticeCore::getScoreboardManager()->setBoxingScoreboard($p);
            }
        }
    }

    /**
     * @param int $tick
     * @return void
     */
    public function update(int $tick): void
    {
        foreach ($this->getPlayers() as $player) {
            $session = PracticeCore::getPlayerSession()::getSession($player);
            if ($player->isOnline()) {
                if ($player->getWorld() === $this->world && ($player->getPosition()->getY() < 98)) {
                    $player->kill();
                }
                if (!$session->isDueling) {
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
        if ($tick % 20 === 0) {
            switch ($this->time) {
                case 903:
                    $this->player1->teleport(new Location(24, 101, 40, $this->world, 180, 0));
                    $this->player2->teleport(new Location(24, 101, 10, $this->world, 0, 0));
                    foreach ($this->getPlayers() as $player) {
                        if ($player instanceof Player) {
                            $player->setGamemode(GameMode::ADVENTURE());
                            $this->kit->setEffect($player);
                            $player->getArmorInventory()->setContents($this->kit->getArmorItems());
                            $player->getInventory()->setContents($this->kit->getInventoryItems());
                            $player->setImmobile();
                            $player->sendTitle('§b3', '', 1, 3, 1);
                            PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $player);
                        }
                    }
                    break;
                case 902:
                    foreach ($this->getPlayers() as $player) {
                        $player->sendTitle('§b2', '', 1, 3, 1);
                        PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $player);
                    }
                    break;
                case 901:
                    foreach ($this->getPlayers() as $player) {
                        $player->sendTitle('§b1', '', 1, 3, 1);
                        PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $player);
                    }
                    break;
                case 900:
                    foreach ($this->getPlayers() as $player) {
                        $player->sendTitle('§bFight!', '', 1, 3, 1);
                        PracticeCore::getInstance()->getPracticeUtils()->playSound('random.anvil_use', $player);
                        $player->setImmobile(false);
                    }
                    break;
                case 0:
                    $this->onEnd();
                    break;
            }
            $this->time--;
        }
    }

    /**
     * @return array<Player>
     */
    public function getPlayers(): array
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
                        $session = PracticeCore::getPlayerSession()::getSession($online);
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
                        $online->setHealth(20);
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
}
