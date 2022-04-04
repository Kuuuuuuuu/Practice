<?php

namespace Kohaku\Entity;

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\math\RayTraceResult;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;

class ArrowEntity extends Arrow
{

    public static function getNetworkTypeId(): string
    {
        return EntityIds::ARROW;
    }

    protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult): void
    {
        $owner = $this->getOwningEntity();
        if ($owner instanceof Player) {
            if ($owner->getWorld() === $this->getWorld() and $owner->isAlive()) {
                parent::onHitEntity($entityHit, $hitResult);
            } else {
                $this->flagForDespawn();
            }
        } else {
            parent::onHitEntity($entityHit, $hitResult);
        }
    }
}