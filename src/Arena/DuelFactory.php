<?php

namespace Kuu\Arena;

use Kuu\Loader;
use Kuu\NeptunePlayer;
use Kuu\Task\NeptuneTask;
use Kuu\Utils\Kits\KitManager;
use pocketmine\player\GameMode;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\world\WorldException;

class DuelFactory
{
    private int $time = 903;
    private NeptunePlayer $player1;
    private NeptunePlayer $player2;
    private World $level;
    private ?NeptunePlayer $winner = null;
    private ?NeptunePlayer $loser = null;
    private KitManager $kit;
    private bool $ended = false;

    public function __construct(string $name, NeptunePlayer $player1, NeptunePlayer $player2, KitManager $kit)
    {
        $world = Server::getInstance()->getWorldManager()->getWorldByName($name);
        if ($world === null) {
            throw new WorldException('World does not exist');
        }
        if (Loader::getCoreTask() instanceof NeptuneTask) {
            Loader::getCoreTask()->addDuelTask($name, $this);
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
                    if ($player instanceof NeptunePlayer) {
                        $player->setGamemode(GameMode::SURVIVAL());
                        $player->getArmorInventory()->setContents($this->kit->getArmorItems());
                        $player->getInventory()->setContents($this->kit->getInventoryItems());
                        $player->setImmobile();
                        $player->sendTitle('§d3', '', 1, 3, 1);
                        Loader::getInstance()->getArenaUtils()->playSound('random.click', $player);
                    }
                }
                $this->level->orderChunkPopulation(15 >> 4, 40 >> 4, null)->onCompletion(function (): void {
                    $this->player1->teleport(new Position(15, 4, 40, $this->level));
                }, function (): void {
                });
                $this->level->orderChunkPopulation(15 >> 4, 10 >> 4, null)->onCompletion(function (): void {
                    $this->player2->teleport(new Position(15, 4, 10, $this->level));
                }, function (): void {
                });
                break;
            case 902:
                foreach ($this->getPlayers() as $player) {
                    if ($player instanceof NeptunePlayer) {
                        $player->setCurrentKit(null);
                    }
                    $player->sendTitle('§d2', '', 1, 3, 1);
                    Loader::getInstance()->getArenaUtils()->playSound('random.click', $player);
                }
                break;
            case 901:
                foreach ($this->getPlayers() as $player) {
                    $player->sendTitle('§d1', '', 1, 3, 1);
                    Loader::getInstance()->getArenaUtils()->playSound('random.click', $player);
                }
                break;
            case 900:
                foreach ($this->getPlayers() as $player) {
                    $player->sendTitle('§dFight!', '', 1, 3, 1);
                    Loader::getInstance()->getArenaUtils()->playSound('random.anvil_use', $player);
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

    public function onEnd(?NeptunePlayer $playerLeft = null): void
    {
        if (!$this->ended) {
            foreach ($this->getPlayers() as $online) {
                if (is_null($playerLeft) or $online->getName() !== $playerLeft->getName()) {
                    if ($online instanceof NeptunePlayer) {
                        $online->sendMessage('§f-----------------------');
                        $winnerMessage = '§aWinner: §f';
                        $winnerMessage .= $this->winner !== null ? $this->winner->getName() : 'None';
                        $online->sendMessage($winnerMessage);
                        $loserMessage = '§cLoser: §f';
                        $loserMessage .= $this->loser !== null ? $this->loser->getName() : 'None';
                        $online->sendMessage($loserMessage);
                        $online->sendMessage('§f-----------------------');
                        Loader::getArenaUtils()->GiveItem($online);
                        Loader::getScoreboardManager()->sb($online);
                        $online->setDueling(false);
                        $online->setCurrentKit(null);
                        $online->setHealth(20);
                        $online->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn(), 0, 0);
                    }
                }
            }
            $this->ended = true;
        }
        Loader::getDuelManager()->stopMatch($this->level->getFolderName());
    }
}