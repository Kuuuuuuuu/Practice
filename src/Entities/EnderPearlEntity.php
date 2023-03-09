<?php

declare(strict_types=1);

namespace Nayuki\Entities;

use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\sound\EndermanTeleportSound;

class EnderPearlEntity extends Throwable
{
    public function __construct(Location $level, ?Entity $owner = null)
    {
        parent::__construct($level, $owner);
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::ENDER_PEARL;
    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool
    {
        $hasUpdate = parent::entityBaseTick($tickDiff);
        $owner = $this->getOwningEntity();
        if ($this->isCollided || $owner === null || !$owner->isAlive() || $owner->isClosed() || ($owner->getWorld() !== $this->getWorld())) {
            $this->flagForDespawn();
        }
        return $hasUpdate;
    }

    /**
     * @param ProjectileHitEvent $event
     * @return void
     */
    public function onHit(ProjectileHitEvent $event): void
    {
        $owner = $this->getOwningEntity();
        if ($owner !== null) {
            $this->getWorld()->addParticle($origin = $owner->getPosition(), new EndermanTeleportParticle());
            $this->getWorld()->addSound($origin, new EndermanTeleportSound());
            if ($owner instanceof Player) {
                $vector = $event->getRayTraceResult()->getHitVector();
                (function () use ($vector): void {
                    $this->setPosition($vector);
                })->call($owner);
                $location = $owner->getLocation();
                $owner->getNetworkSession()->syncMovement($location, $location->yaw, $location->pitch);
                $this->setOwningEntity(null);
            }
            $owner->attack(new EntityDamageEvent($owner, EntityDamageEvent::CAUSE_FALL, 5));
        }
    }
}
