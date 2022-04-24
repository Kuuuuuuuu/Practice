<?php


namespace Kuu\Entity;

use JetBrains\PhpStorm\Pure;
use Kuu\Items\FishingRod;
use Kuu\NeptunePlayer;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\RayTraceResult;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\utils\Random;

class FishingHook extends Projectile
{
    protected $gravity = 0.08;
    protected $drag = 0.05;

    public function __construct(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $shootingEntity, $nbt);
        $this->motion->x = -sin(deg2rad($location->yaw)) * cos(deg2rad($location->pitch));
        $this->motion->y = -sin(deg2rad($location->pitch));
        $this->motion->z = cos(deg2rad($location->yaw)) * cos(deg2rad($location->pitch));
        if ($shootingEntity instanceof NeptunePlayer) {
            $this->handleHookCasting($this->motion->x, $this->motion->y, $this->motion->z, 1.5, 1.0);
        }
    }

    public function handleHookCasting(float $x, float $y, float $z, float $ff1, float $ff2): void
    {
        $rand = new Random();
        $x = $x + $rand->nextSignedFloat() * 0.007499999832361937 * $ff2;
        $y = $y + $rand->nextSignedFloat() * 0.007499999832361937 * $ff2;
        $z = $z + $rand->nextSignedFloat() * 0.007499999832361937 * $ff2;
        $x *= $ff1;
        $y *= $ff1;
        $z *= $ff1;
        $this->motion->x = $x;
        $this->motion->y = $y;
        $this->motion->z = $z;
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::FISHING_HOOK;
    }

    public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult): void
    {
        $entityHit->attack(new EntityDamageByEntityEvent($this, $entityHit, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 0));
        if ($entityHit === $this->getOwningEntity()) {
            $this->flagForDespawn();
            return;
        }
        $this->isCollided = true;
        $this->setTargetEntity($entityHit);
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        $hasUpdate = parent::entityBaseTick($tickDiff);
        $owner = $this->getOwningEntity();
        if ($owner instanceof Player) {
            if ($owner->getPosition()->asVector3()->distance($this->getPosition()) > 10 || $owner->isClosed() || !$owner->isAlive() || !($owner->getInventory()->getItemInHand() instanceof FishingRod)) {
                $this->flagForDespawn();
            }
        } else {
            $this->flagForDespawn();
        }
        return $hasUpdate;
    }

    public function handleHookRetraction(): void
    {
        $angler = $this->getOwningEntity();
        if ($angler instanceof Player) {
            $target = $this->getTargetEntity();
            if ($target !== null) {
                $dx = $angler->getPosition()->getX() - $this->getPosition()->getX();
                $dy = $angler->getPosition()->getY() - $this->getPosition()->getY();
                $dz = $angler->getPosition()->getZ() - $this->getPosition()->getZ();
                $sqrt = sqrt($dx * $dx + $dy * $dy + $dz * $dz);
                $target->setMotion(
                    $target->motion->add(
                        $dx * 0.1,
                        $dy * 0.1 + sqrt($sqrt) * 0.08,
                        $dz * 0.1
                    )
                );
            }
        }
    }

    #[Pure] protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.25, 0.25);
    }
}