<?php

namespace Kohaku\Task;

use Kohaku\Loader;
use Kohaku\NeptunePlayer;
use Kohaku\Utils\Kits\KitManager;
use pocketmine\player\GameMode;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\world\WorldException;

class DuelTask extends Task
{
    private int $time = 903;
    private int $tick = 0;
    private NeptunePlayer $player1;
    private NeptunePlayer $player2;
    private World $level;
    private ?NeptunePlayer $winner = null;
    private ?NeptunePlayer $loser = null;
    private KitManager $kit;

    public function __construct(string $name, NeptunePlayer $player1, NeptunePlayer $player2, KitManager $kit)
    {
        $world = Server::getInstance()->getWorldManager()->getWorldByName($name);
        if ($world === null) {
            throw new WorldException("World does not exist");
        }
        $this->setHandler(Loader::getInstance()->getScheduler()->scheduleRepeatingTask($this, 1));
        $this->level = $world;
        $this->kit = $kit;
        $this->player1 = $player1;
        $this->player2 = $player2;
    }

    public function onRun(): void
    {
        $this->tick++;
        if ($this->tick % 40 === 0) {
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
        }
        if ($this->tick % 20 === 0) {
            switch ($this->time) {
                case 902:
                    foreach ($this->getPlayers() as $player) {
                        if ($player instanceof NeptunePlayer) {
                            $player->setGamemode(GameMode::SURVIVAL());
                            $player->getArmorInventory()->setContents($this->kit->getArmorItems());
                            $player->getInventory()->setContents($this->kit->getInventoryItems());
                            $player->setImmobile(true);
                            $player->sendTitle("§d3", "", 1, 3, 1);
                            Loader::getInstance()->getArenaUtils()->playSound("random.click", $player);
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
                case 901:
                    foreach ($this->getPlayers() as $player) {
                        if ($player instanceof NeptunePlayer) {
                            $player->setCurrentKit(null);
                        }
                        $player->sendTitle("§d2", "", 1, 3, 1);
                        Loader::getInstance()->getArenaUtils()->playSound("random.click", $player);
                    }
                    break;
                case 900:
                    foreach ($this->getPlayers() as $player) {
                        $player->sendTitle("§d1", "", 1, 3, 1);
                        Loader::getInstance()->getArenaUtils()->playSound("random.click", $player);
                    }
                    break;
                case 899:
                    foreach ($this->getPlayers() as $player) {
                        $player->sendTitle("§dFight!", "", 1, 3, 1);
                        Loader::getInstance()->getArenaUtils()->playSound("random.anvil_use", $player);
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

    public function onEnd(?NeptunePlayer $playerLeft = null): void
    {
        foreach ($this->getPlayers() as $online) {
            if (is_null($playerLeft) or $online->getName() !== $playerLeft->getName()) {
                if ($online instanceof NeptunePlayer) {
                    $online->sendMessage("§f-----------------------");
                    $winnerMessage = "§aWinner: §f";
                    $winnerMessage .= $this->winner !== null ? $this->winner->getName() : "None";
                    $online->sendMessage($winnerMessage);
                    $loserMessage = "§cLoser: §f";
                    $loserMessage .= $this->loser !== null ? $this->loser->getName() : "None";
                    $online->sendMessage($loserMessage);
                    $online->sendMessage("§f-----------------------");
                    Loader::getArenaUtils()->GiveItem($online);
                    Loader::getScoreboardManager()->sb($online);
                    $online->setDueling(false);
                    $online->setCurrentKit(null);
                    $online->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn(), 0, 0);
                }
            }
        }
        Loader::getDuelManager()->stopMatch($this->level->getFolderName());
    }
}