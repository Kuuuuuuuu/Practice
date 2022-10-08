<?php

namespace Kuu\Entity;

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\math\RayTraceResult;
use pocketmine\player\Player;

class ArrowEntity extends Arrow
{

    public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult): void
    {
        $owner = $this->getOwningEntity();
        if ($owner instanceof Player) {
            if ($owner->getWorld() === $this->getWorld() && $owner->isAlive()) {
                parent::onHitEntity($entityHit, $hitResult);
            } else {
                $this->flagForDespawn();
            }
        }
    }
}