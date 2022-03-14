<?php

declare(strict_types=1);

namespace Kohaku\Core\Task;

use Kohaku\Core\Entity\FallingWool;
use Kohaku\Core\Utils\ArenaUtils;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\World;

class ParkourFinishTask extends Task
{

    private Player $player;
    private World $world;
    private array $fallingBlocks = [];
    private int $amount = 30;

    public function __construct(Player $player, World $world)
    {
        $this->player = $player;
        $this->world = $world;
    }

    public function onRun(): void
    {
        if ($this->isExecutable()) {
            $location = $this->player->getLocation();
            $fallingWool = ArenaUtils::generateFallingWoolBlock($location);
            $this->fallingBlocks[] = $fallingWool;
            ArenaUtils::playSound("liquid.lavapop", $this->player);
            $this->amount--;
            return;
        }
        $this->getHandler()->cancel();
        foreach ($this->fallingBlocks as $fallingBlock) {
            if (!$fallingBlock instanceof FallingWool) {
                continue;
            }
            if (!$fallingBlock->isFlaggedForDespawn()) {
                $fallingBlock->flagForDespawn();
                $fallingBlock->close();
            }
        }
    }

    private function isExecutable(): bool
    {
        $player = $this->player;
        $amount = $this->amount;
        return ($amount >= 1) && ($player->isOnline()) && ($player->getWorld() === $this->world);
    }
}