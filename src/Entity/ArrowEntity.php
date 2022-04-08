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
                if ($this->punchKnockback > 0) {
                    $horizontalSpeed = sqrt($this->motion->x ** 2 + $this->motion->z ** 2);
                    if ($horizontalSpeed > 0) {
                        $multiplier = $this->punchKnockback * 0.6 / $horizontalSpeed;
                        $entityHit->setMotion($entityHit->getMotion()->add($this->motion->x * $multiplier, 0.1, $this->motion->z * $multiplier));
                    }
                }
            } else {
                $this->flagForDespawn();
            }
        }
    }
}