<?php

namespace Kohaku\Entity;

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

class FistBot extends Human
{

    private string $target;
    private float $speed = 0.7;

    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null, string $target = '')
    {
        parent::__construct($location, $skin, $nbt);
        $this->target = $target;
        $this->alwaysShowNameTag = true;
        $this->gravityEnabled = true;
        $this->gravity = 0.079;
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        parent::entityBaseTick($tickDiff);
        if (!$this->isAlive() or $this->getTargetPlayer() === null or !$this->getTargetPlayer()->isAlive()) {
            if (!$this->closed) {
                $this->flagForDespawn();
            }
            return false;
        }
        $position = $this->getTargetPlayer()->getPosition()->asVector3();
        $x = $position->x - $this->getLocation()->getX();
        $z = $position->z - $this->getLocation()->getZ();
        if ($x != 0 or $z != 0) {
            $this->motion->x = $this->getSpeed() * 0.4 * ($x / (abs($x) + abs($z)));
            $this->motion->z = $this->getSpeed() * 0.4 * ($z / (abs($x) + abs($z)));
        }
        $health = round($this->getHealth());
        $this->setNameTag(TextFormat::BOLD . '§dFistBot ' . "\n" . TextFormat::RED . "$health");
        if ($this->getTargetPlayer() === null or $this->getTargetPlayer()->getWorld() !== $this->getWorld()) {
            $this->flagForDespawn();
            return false;
        } else {
            $this->attackTargetPlayer();
            if (!$this->isSprinting()) {
                $this->setSprinting();
            }
        }
        return $this->isAlive();
    }

    private function getTargetPlayer(): Player
    {
        return Server::getInstance()->getPlayerByPrefix($this->target);
    }

    private function getSpeed(): float
    {
        return $this->speed;
    }

    private function attackTargetPlayer(): void
    {
        if (mt_rand(0, 100) % 5 === 0) {
            $this->lookAt($this->getTargetPlayer()->getPosition()->asVector3());
        }
        if ($this->isLookingAt($this->getTargetPlayer()->getPosition()->asVector3())) {
            if ($this->getLocation()->distance($this->getTargetPlayer()->getPosition()->asVector3()) <= 2.4) {
                $this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
                $event = new EntityDamageByEntityEvent($this, $this->getTargetPlayer(), EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getInventory()->getItemInHand()->getAttackPoints());
                $this->broadcastMotion();
                $this->getTargetPlayer()->attack($event);
            }
        }
    }

    private function isLookingAt(Vector3 $target): bool
    {
        $xDist = $target->x - $this->getLocation()->getX();
        $zDist = $target->z - $this->getLocation()->getZ();
        $expectedYaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
        if ($expectedYaw < 0) {
            $expectedYaw += 360.0;
        }
        return 2.5 && abs($expectedYaw - $this->getLocation()->getYaw()) <= 10;
    }

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
                $deltaX = $this->getPosition()->getX() - $killer->getPosition()->getX();
                $deltaZ = $this->getPosition()->getZ() - $killer->getPosition()->getZ();
                $this->knockBack($deltaX, $deltaZ, 0.45);
            }
        }
    }

    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void
    {
        $xzKB = 0.299;
        $yKb = 0.301;
        $f = sqrt($x * $x + $z * $z);
        if ($f <= 0) {
            return;
        }
        if (mt_rand() / mt_getrandmax() > $this->knockbackResistanceAttr->getValue()) {
            $f = 1 / $f;
            $motion = clone $this->motion;
            $motion->x /= 2;
            $motion->y /= 2;
            $motion->z /= 2;
            $motion->x += $x * $f * $xzKB;
            $motion->y += $yKb;
            $motion->z += $z * $f * $xzKB;
            if ($motion->y > $yKb) {
                $motion->y = $yKb;
            }
            if ($this->isAlive() and !$this->isClosed()) {
                $this->move(0, 0, 0);
                $this->setMotion($motion);
            }
        }
    }

    public function getName(): string
    {
        return 'FistBot';
    }
}