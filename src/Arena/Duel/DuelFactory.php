<?php

namespace Kuu\Arena\Duel;

use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use Kuu\Task\PracticeTask;
use Kuu\Utils\Kits\KitManager;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Location;
use pocketmine\player\GameMode;
use pocketmine\Server;
use pocketmine\world\World;
use pocketmine\world\WorldException;

class DuelFactory extends DuelFactoryBase
{
    private int $time = 903;
    private PracticePlayer $player1;
    private PracticePlayer $player2;
    private World $level;
    private ?PracticePlayer $winner = null;
    private ?PracticePlayer $loser = null;
    private KitManager $kit;
    private bool $ended = false;

    public function __construct(string $name, PracticePlayer $player1, PracticePlayer $player2, KitManager $kit)
    {
        $world = Server::getInstance()->getWorldManager()->getWorldByName($name);
        if ($world === null) {
            throw new WorldException('World does not exist');
        }
        if (PracticeCore::getCoreTask() instanceof PracticeTask) {
            PracticeCore::getCoreTask()?->addDuelTask($name, $this);
        }
        $this->level = $world;
        $this->kit = $kit;
        $this->player1 = $player1;
        $this->player2 = $player2;
    }

    public function update(int $tick): void
    {
        foreach ($this->getPlayers() as $player) {
            if ($player->isOnline()) {
                if (($player->getPosition()->getY() < 98) && $player->getWorld() === $this->level) {
                    $player->kill();
                }
                if (!$player->isDueling()) {
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
                    foreach ($this->getPlayers() as $player) {
                        if ($player instanceof PracticePlayer) {
                            $player->setGamemode(GameMode::SURVIVAL());
                            $player->getArmorInventory()->setContents($this->kit->getArmorItems());
                            $player->getInventory()->setContents($this->kit->getInventoryItems());
                            $player->setImmobile();
                            $player->sendTitle('§b3', '', 1, 3, 1);
                            PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $player);
                            if ($this->kit->getName() === 'Sumo') {
                                $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 100000, 255, false));
                            }
                        }
                    }
                    if ($this->kit->getName() === 'Sumo') {
                        $this->player1->teleport(new Location(6, 101, 0, $this->level, 0, 0));
                        $this->player2->teleport(new Location(6, 101, 9, $this->level, 180, 0));
                    } else {
                        $this->player1->teleport(new Location(24, 101, 40, $this->level, 180, 0));
                        $this->player2->teleport(new Location(24, 101, 10, $this->level, 0, 0));
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

    public function getPlayers(): array
    {
        return [$this->player1, $this->player2];
    }

    public function onEnd(?PracticePlayer $playerLeft = null): void
    {
        if (!$this->ended) {
            foreach ($this->getPlayers() as $online) {
                if (is_null($playerLeft) || $online->getName() !== $playerLeft->getName()) {
                    if ($online instanceof PracticePlayer) {
                        $online->sendMessage('§f-----------------------');
                        $winnerMessage = '§aWinner: §f';
                        $winnerMessage .= $this->winner !== null ? $this->winner->getName() : 'None';
                        $online->sendMessage($winnerMessage);
                        $loserMessage = '§cLoser: §f';
                        $loserMessage .= $this->loser !== null ? $this->loser->getName() : 'None';
                        $online->sendMessage($loserMessage);
                        $online->sendMessage('§f-----------------------');
                        $online->setLobbyItem();
                        PracticeCore::getScoreboardManager()->sb($online);
                        $online->setDueling(false);
                        $online->setCurrentKit(null);
                        $online->setHealth(20);
                        $online->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()?->getSafeSpawn(), 0, 0);
                    }
                }
            }
            $this->ended = true;
        }
        PracticeCore::getDuelManager()->stopMatch($this->level->getFolderName());
    }
}