<?php

declare(strict_types=1);

namespace Nayuki\Entities;

use Exception;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

final class PracticeBot extends Human
{
    /** @var string */
    private string $target;
    /** @var float */
    private float $speed = 0.85;

    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null, string $target = '')
    {
        parent::__construct($location, $skin, $nbt);
        $this->target = $target;
        $this->alwaysShowNameTag = true;
        $this->gravityEnabled = true;
    }

    /**
     * @throws Exception
     */
    public function entityBaseTick(int $tickDiff = 1): bool
    {
        parent::entityBaseTick($tickDiff);
        if (!$this->isAlive() || $this->getTargetPlayer() === null || !$this->getTargetPlayer()->isAlive() || $this->getTargetPlayer()->getWorld() !== $this->getWorld()) {
            if (!$this->closed) {
                $this->flagForDespawn();
            }
            return false;
        }
        $health = round($this->getHealth());
        $this->setNameTag('§bPracticeBot ' . "\n" . TextFormat::RED . $health);
        $this->attackTargetPlayer();
        if (!$this->isSprinting()) {
            $this->setSprinting();
        }
        if ($tickDiff % 10 === 0) {
            $target = $this->getTargetPlayer();
            if ($target === null) {
                if (!$this->closed) {
                    $this->flagForDespawn();
                }
                return false;
            }
            $position = $target->getPosition();
            $location = $this->getLocation();
            $x = $position->x - $location->getX();
            $z = $position->z - $location->getZ();
            $y = $position->y - $location->getY();
            $absX = abs($x);
            $absZ = abs($z);
            $maxAbs = max($absX, $absZ);
            $speed = $this->speed * ($target->isSprinting() ? 0.4 : 0.35);
            $this->motion->x = $speed * ($x / $maxAbs);
            $this->motion->z = $speed * ($z / $maxAbs);
            $sqrtXZ = sqrt($x * $x + $z * $z);
            $this->location->yaw = rad2deg(atan2(-$x, $z));
            $this->location->pitch = rad2deg(-atan2($y, $sqrtXZ));
        }
        return $this->isAlive();
    }

    /**
     * @return Player|null
     */
    private function getTargetPlayer(): ?Player
    {
        return Server::getInstance()->getPlayerExact($this->target);
    }

    /**
     * @return void
     */
    private function attackTargetPlayer(): void
    {
        $target = $this->getTargetPlayer();
        if ($target === null) {
            return;
        }
        if (!$target->isOnline() || !$target->isAlive() || $target->getWorld() !== $this->getWorld()) {
            return;
        }
        $targetVector = $target->getPosition()->asVector3();
        if ($this->isLookingAt($targetVector) && $this->getLocation()->distance($targetVector) <= 3.2) {
            $this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
            $event = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getInventory()->getItemInHand()->getAttackPoints());
            $this->broadcastMotion();
            $target->attack($event);
        }
    }

    /**
     * @param Vector3 $target
     * @return bool
     */
    private function isLookingAt(Vector3 $target): bool
    {
        $location = $this->getLocation();
        $xDist = $target->getX() - $location->getX();
        $zDist = $target->getZ() - $location->getZ();
        $expectedYaw = atan2($zDist, $xDist) * 180.0 / M_PI - 90.0;
        $expectedYaw = $expectedYaw < 0.0 ? $expectedYaw + 360.0 : $expectedYaw;
        $currentYaw = $location->getYaw();
        return abs($currentYaw - $expectedYaw) <= 10.0;
    }

    /**
     * @param EntityDamageEvent $source
     * @return void
     */
    public function attack(EntityDamageEvent $source): void
    {
        parent::attack($source);
        if ($source->isCancelled()) {
            $source->cancel();
            return;
        }
        if ($source instanceof EntityDamageByEntityEvent) {
            $killer = $source->getDamager();
            if ($killer instanceof Player) {
                $pos = $this->getPosition();
                $deltaX = $pos->getX() - $killer->getPosition()->getX();
                $deltaZ = $pos->getZ() - $killer->getPosition()->getZ();
                $this->knockBack($deltaX, $deltaZ);
            }
        }
    }

    /**
     * @param float $x
     * @param float $z
     * @param float $force
     * @param float|null $verticalLimit
     * @return void
     */
    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void
    {
        $xzKB = 0.393;
        $yKb = 0.398;
        $f = sqrt($x * $x + $z * $z);
        if ($f > 0 && (mt_rand() / mt_getrandmax()) > $this->knockbackResistanceAttr->getValue()) {
            $kbFactor = 1 / $f;
            $xzKbFactor = $kbFactor * $xzKB;
            $motion = clone $this->motion;
            $motion->x = ($motion->x + $x * $xzKbFactor) / 2;
            $motion->y = ($motion->y + $yKb) / 2;
            $motion->z = ($motion->z + $z * $xzKbFactor) / 2;
            $motion->y = min($motion->y, $yKb);
            $this->setMotion($motion);
        }
    }

    public function getName(): string
    {
        return 'PracticeBot';
    }
}
