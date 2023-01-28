<?php

declare(strict_types=1);

namespace Nayuki\Entities;

use Nayuki\PracticeCore;
use pocketmine\entity\Location;
use pocketmine\entity\Zombie;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;

final class JoinEntity extends Zombie
{

    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $nbt);
        $this->setNameTagAlwaysVisible();
    }

    /**
     * @param CompoundTag $nbt
     * @return void
     */
    public function initEntity(CompoundTag $nbt): void
    {
        $this->setHealth(20);
        $this->setMaxHealth(20);
        $this->setScale(1.5);
        $this->setNameTag("§ePractice\n§7Click to Play");
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
