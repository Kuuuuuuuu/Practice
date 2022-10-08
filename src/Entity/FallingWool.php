<?php

declare(strict_types=1);

namespace Kuu\Entity;

use pocketmine\entity\object\FallingBlock;
use pocketmine\nbt\tag\CompoundTag;

class FallingWool extends FallingBlock
{

    public function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->setForceMovementUpdate();
        $this->setSilent();
        $this->setCanSaveWithChunk(false);
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->isClosed()) {
            return false;
        }
        if (!$this->isFlaggedForDespawn() && $this->onGround) {
            $this->flagForDespawn();
            $this->close();
        }
        return true;
    }
}