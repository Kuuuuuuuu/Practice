<?php

namespace Kuu\Arena;

use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use Kuu\Task\PracticeTask;
use Kuu\Utils\Kits\KitManager;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\GameMode;
use pocketmine\Server;
use pocketmine\world\Position;
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
    private int $z = 40;
    private int $z2 = 20;

    public function __construct(string $name, PracticePlayer $player1, PracticePlayer $player2, KitManager $kit)
    {
        $world = Server::getInstance()->getWorldManager()->getWorldByName($name);
        if ($world === null) {
            throw new WorldException('World does not exist');
        }
        if (PracticeCore::getCoreTask() instanceof PracticeTask) {
            PracticeCore::getCoreTask()?->addDuelTask($name, $this);
        }
        if ($kit->getName() === 'Sumo') {
            $this->z = 0;
            $this->z2 = 9;
        }
        $this->level = $world;
        $this->kit = $kit;
        $this->player1 = $player1;
        $this->player2 = $player2;
    }

    public function update(): void
    {
        foreach ($this->getPlayers() as $player) {
            if ($player->isOnline()) {
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
        switch ($this->time) {
            case 903:
                foreach ($this->getPlayers() as $player) {
                    if ($player instanceof PracticePlayer) {
                        $player->setGamemode(GameMode::SURVIVAL());
                        $player->getArmorInventory()->setContents($this->kit->getArmorItems());
                        $player->getInventory()->setContents($this->kit->getInventoryItems());
                        $player->setImmobile();
                        $player->sendTitle('§d3', '', 1, 3, 1);
                        PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $player);
                        if ($this->kit->getName() === 'Sumo') {
                            $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 1000, 255, false));
                        }
                    }
                }
                $this->level->orderChunkPopulation(6 >> 4, $this->z >> 4, null)->onCompletion(function (): void {
                    $this->player1->teleport(new Position(6, 4, $this->z, $this->level), 180);
                }, static function (): void {
                });
                $this->level->orderChunkPopulation(6 >> 4, $this->z2 >> 4, null)->onCompletion(function (): void {
                    $this->player2->teleport(new Position(6, 4, $this->z2, $this->level), 180);
                }, static function (): void {
                });
                break;
            case 902:
                foreach ($this->getPlayers() as $player) {
                    if ($player instanceof PracticePlayer) {
                        $player->setCurrentKit(null);
                    }
                    $player->sendTitle('§d2', '', 1, 3, 1);
                    PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $player);
                }
                break;
            case 901:
                foreach ($this->getPlayers() as $player) {
                    $player->sendTitle('§d1', '', 1, 3, 1);
                    PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $player);
                }
                break;
            case 900:
                foreach ($this->getPlayers() as $player) {
                    $player->sendTitle('§dFight!', '', 1, 3, 1);
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
                        PracticeCore::getPracticeUtils()->GiveLobbyItem($online);
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