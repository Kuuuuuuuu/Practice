<?php

namespace Kohaku\Core\Task;

use Kohaku\Core\Arena\DuelManager;
use Kohaku\Core\HorizonPlayer;
use Kohaku\Core\Loader;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TF;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\world\WorldException;

class DuelTask extends Task
{

    private int $time = 903;
    private HorizonPlayer $player1;
    private HorizonPlayer $player2;
    private World $level;
    private ?HorizonPlayer $winner = null;
    private ?HorizonPlayer $loser = null;

    public function __construct(Loader $plugin, string $name, HorizonPlayer $player1, HorizonPlayer $player2)
    {
        $world = $plugin->getServer()->getWorldManager()->getWorldByName($name);
        if ($world === null) {
            throw new WorldException("World does not exist");
        }
        $this->setHandler($plugin->getScheduler()->scheduleRepeatingTask($this, 20));
        $this->level = $world;
        $this->player1 = $player1;
        $this->player2 = $player2;
    }

    public function onRun(): void
    {
        foreach ($this->getPlayers() as $player) {
            if ($player->isOnline()) {
                if (!$player->isPlaying()) {
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
            case 902:
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

    public function getPlayers(): array
    {
        return [$this->player1, $this->player2];
    }

    public function onEnd(?HorizonPlayer $playerLeft = null): void
    {
        foreach ($this->getPlayers() as $online) {
            if (is_null($playerLeft) || $online->getName() !== $playerLeft->getName()) {
                $online->sendMessage(TF::GRAY . "---------------");
                $winnerMessage = TF::GOLD . "Winner: " . TF::WHITE;
                if ($this->winner === null) {
                    $winnerMessage .= "None";
                } else {
                    $winnerMessage .= $this->winner->getDisplayName() . " " . floor($this->winner->getHealth() / 2) . TF::RED . " ❤";
                }
                $online->sendMessage($winnerMessage);
                $loserMessage = TF::YELLOW . "Loser: " . TF::WHITE;
                $loserMessage .= $this->loser !== null ? $this->loser->getDisplayName() : "None";
                $online->sendMessage($loserMessage);
                $online->sendMessage(TF::GRAY . "---------------");
                $online->resetPlayer();
                $online->giveLobbyItems();
                $online->teleport($online->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn(), 0, 0);
            }
        }
        $this->getHandler()->cancel();
        DuelManager::getInstance()->stopMatch($this->level->getFolderName());
    }
}