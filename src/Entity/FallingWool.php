<?php

declare(strict_types=1);

namespace Kohaku\Core\Entity;

use pocketmine\entity\object\FallingBlock;
use pocketmine\nbt\tag\CompoundTag;

class FallingWool extends FallingBlock {

    protected function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->setForceMovementUpdate(true);
        $this->setSilent(true);
        $this->setCanSaveWithChunk(false);
    }

    protected function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->isClosed()) {
            return false;
        }

        if (!$this->isFlaggedForDespawn()) {
            if ($this->onGround) {
                $this->flagForDespawn();
            }
        }
        return true;
    }
}