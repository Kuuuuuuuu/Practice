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
    private float $speed = 0.45;
    /** @var int */
    private int $tick = 0;

    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null, string $target = '')
    {
        parent::__construct($location, $skin, $nbt);
        $this->target = $target;
        $this->alwaysShowNameTag = true;
        $this->gravityEnabled = true;
    }

    public function getName(): string
    {
        return 'PracticeBot';
    }

    /**
     * @throws Exception
     */
    protected function entityBaseTick(int $tickDiff = 1): bool
    {
        ++$this->tick;
        parent::entityBaseTick($tickDiff);

        // Check if the bot should despawn
        if (!$this->isAlive() || ($target = $this->getTargetPlayer()) === null || !$target->isAlive() || $target->getWorld() !== $this->getWorld()) {
            if (!$this->closed) {
                $this->flagForDespawn();
            }
            return $this->isAlive() && !$this->closed;
        }

        $this->updateBotNameTag();
        $this->attackTargetPlayer();
        $this->calculateMovement($target->getPosition()->asVector3());

        return $this->isAlive() && !$this->closed;
    }

    /**
     * @return Player|null
     */
    private function getTargetPlayer(): ?Player
    {
        return Server::getInstance()->getPlayerExact($this->target);
    }

    protected function updateBotNameTag(): void
    {
        $this->setNameTag(TextFormat::LIGHT_PURPLE . 'Bot' . ' ' . TextFormat::WHITE . '[' . TextFormat::RED . round($this->getHealth()) . TextFormat::WHITE . '/' . TextFormat::RED . round($this->getMaxHealth()) . TextFormat::WHITE . ']');
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
        if ($this->isLookingAt($targetVector) && $this->getLocation()->distance($targetVector) <= 2.85) {
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
        $entityLocation = $this->getLocation();
        $targetXDistance = $target->x - $entityLocation->x;
        $targetZDistance = $target->z - $entityLocation->z;

        $expectedYaw = rad2deg(atan2($targetZDistance, $targetXDistance)) - 90.0;
        $expectedYaw += ($expectedYaw < 0.0) ? 360.0 : 0.0;

        $currentYaw = $entityLocation->yaw;

        $yawDifference = abs($currentYaw - $expectedYaw);

        return $yawDifference <= 15.0;
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

    protected function calculateMovement(Vector3 $targetPosition): void
    {
        $position = $this->getPosition()->asVector3();
        $xDifference = $targetPosition->x - $position->getX();
        $zDifference = $targetPosition->z - $position->getZ();
        $yDifference = $targetPosition->y - $position->getY();

        $speed = $this->speed;

        if (!$this->isSprinting()) {
            $this->setSprinting();
        }

        $xzSum = abs($xDifference) + abs($zDifference);
        if ($xzSum !== 0) {
            $xzRatio = $xDifference / $xzSum;
            $this->motion->x = $speed * 0.34 * $xzRatio;
            $this->motion->z = $speed * 0.34 * ($zDifference / $xzSum);
        }

        $sqrtXZ = sqrt($xDifference * $xDifference + $zDifference * $zDifference);
        if ($sqrtXZ != 0) {
            $this->location->yaw = rad2deg(atan2(-$xDifference, $zDifference));
            $this->location->pitch = rad2deg(-atan2($yDifference, $sqrtXZ));
        }
    }
}
