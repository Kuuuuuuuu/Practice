<?php

declare(strict_types=1);

namespace Nayuki\Entities;

use pocketmine\entity\Human;
use pocketmine\nbt\tag\CompoundTag;

final class JoinEntity extends Human
{
    /**
     * @param CompoundTag $nbt
     * @return void
     */
    public function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
    }

    public function onUpdate(int $currentTick): bool
    {
        $this->setNameTagAlwaysVisible();
        $this->setNameTag("§ePractice\n§7Click to Play");
        $this->setScale(1.5);
        return parent::onUpdate($currentTick);
    }
}
