<?php

namespace Kohaku\Entity;

use pocketmine\entity\projectile\Throwable;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\sound\EndermanTeleportSound;

class EnderPearlEntity extends Throwable
{
    public static function getNetworkTypeId(): string
    {
        return EntityIds::ENDER_PEARL;
    }

    protected function onHit(ProjectileHitEvent $event): void
    {
        $owner = $this->getOwningEntity();
        if ($owner !== null) {
            if ($owner->getWorld() !== $this->getWorld()) {
                $this->flagForDespawn();
                return;
            }
            $this->getWorld()->addParticle($origin = $owner->getPosition(), new EndermanTeleportParticle());
            $this->getWorld()->addSound($origin, new EndermanTeleportSound());
            $owner->teleport($target = $event->getRayTraceResult()->getHitVector());
            $this->getWorld()->addSound($target, new EndermanTeleportSound());
            $owner->attack(new EntityDamageEvent($owner, EntityDamageEvent::CAUSE_FALL, 5));
        }
    }
}
