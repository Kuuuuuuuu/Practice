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
    private float $speed = 0.7;
    /** @var int */
    private int $tick = 0;

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
        $this->tick++;
        parent::entityBaseTick($tickDiff);
        $target = $this->getTargetPlayer();
        if (!$this->isAlive() || $target === null || !$target->isAlive() || $target->getWorld() !== $this->getWorld()) {
            if (!$this->closed) {
                $this->flagForDespawn();
            }
            return false;
        }
        $this->setNameTag('§bPracticeBot ' . "\n" . TextFormat::RED . round($this->getHealth()));
        $this->attackTargetPlayer();
        $targetPosition = $target->getPosition()->asVector3();
        $position = $this->getPosition()->asVector3();
        $x = $targetPosition->x - $position->getX();
        $z = $targetPosition->z - $position->getZ();
        $y = $targetPosition->y - $position->getY();
        $speed = $this->speed;
        if ($this->tick % 3 === 0 && $x != 0 && $z != 0) {
            if (!$this->isSprinting()) {
                $this->setSprinting();
            }
            $this->motion->x = $speed * 0.34 * ($x / (abs($x) + abs($z)));
            $this->motion->z = $speed * 0.34 * ($z / (abs($x) + abs($z)));
        }
        $sqrtXZ = sqrt($x * $x + $z * $z);
        $this->location->yaw = rad2deg(atan2(-$x, $z));
        $this->location->pitch = rad2deg(-atan2($y, $sqrtXZ));
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
        if ($this->isLookingAt($targetVector) && $this->getLocation()->distance($targetVector) <= 2.8) {
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
        $xzKB = 0.411;
        $yKb = 0.432;
        $f = sqrt($x * $x + $z * $z);
        if ($f > 0 && mt_rand() / mt_getrandmax() > $this->knockbackResistanceAttr->getValue()) {
            $f = 1 / $f;
            $motion = clone $this->motion;
            $motion->x = ($motion->x / 2) + ($x * $f * $xzKB);
            $motion->y = ($motion->y / 2) + $yKb;
            $motion->z = ($motion->z / 2) + ($z * $f * $xzKB);
            $motion->y = min($motion->y, $yKb);
            $motion->x = $motion->x * 0.325;
            $motion->z = $motion->z * 0.325;
            $this->setMotion($motion);
        }
    }

    public function getName(): string
    {
        return 'PracticeBot';
    }
}
