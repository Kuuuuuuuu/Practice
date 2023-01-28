<?php

declare(strict_types=1);

namespace Nayuki\Entities;

use Nayuki\PracticeCore;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;

final class JoinEntity extends Human
{
    /**
     * @param CompoundTag $nbt
     * @return void
     */
    public function initEntity(CompoundTag $nbt): void
    {
        $this->setNameTagAlwaysVisible();
        $this->setNameTag("§ePractice\n§7Click to Play");
        $this->setScale(1.5);
        parent::initEntity($nbt);
    }

    public function attack(EntityDamageEvent $source): void
    {
        $entity = $source->getEntity();
        if ($entity instanceof Player) {
            PracticeCore::getFormUtils()->ArenaForm($entity);
        }
    }
}
