<?php

namespace Kohaku\Task;

use Kohaku\NeptunePlayer;
use Kohaku\Loader;
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
                /* @var $online NeptunePlayer */
                $online->setCurrentKit(null);
                $online->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn(), 0, 0);
            }
        }
        if (!$this->getHandler()->isCancelled()) {
            $this->getHandler()->cancel();
        }
        Loader::getInstance()->getDuelManager()->stopMatch($this->level->getFolderName());
    }
}