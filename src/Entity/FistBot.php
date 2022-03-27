<?php

namespace Kohaku\Core\Entity;

use Kohaku\Core\Loader;
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
    private int $hitTick = 0;
    private float $speed = 0.5;
    private int $tick = 0;
    private float $yKb = 0.32;
    private float $hKb = 0.32;

    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null, string $target = "")
    {
        parent::__construct($location, $skin, $nbt);
        $this->target = $target;
        $this->alwaysShowNameTag = true;
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        parent::entityBaseTick($tickDiff);
        $this->tick++;
        if (!$this->isAlive() or $this->getTargetPlayer() === null or !$this->getTargetPlayer()->isAlive()) {
            if (!$this->closed) {
                $this->flagForDespawn();
            }
            return false;
        }
        $position = $this->getTargetPlayer()->getPosition()->asVector3();
        $x = $position->x - $this->getLocation()->getX();
        $z = $position->z - $this->getLocation()->getZ();
        if ($x != 0 || $z != 0) {
            $this->motion->x = $this->getSpeed() * 0.35 * ($x / (abs($x) + abs($z)));
            $this->motion->z = $this->getSpeed() * 0.35 * ($z / (abs($x) + abs($z)));
        }
        if (!$this->recentlyHit()) {
            $this->move($this->motion->x, $this->motion->y, $this->motion->z);
        }
        if ($this->tick % 40 === 0) {
            $roundedHealth = round($this->getHealth());
            $this->setNameTag(TextFormat::BOLD . "§bPracticeBot " . "\n" . TextFormat::RED . "$roundedHealth");
            if ($this->getLocation()->distance($this->getTargetPlayer()->getPosition()->asVector3()) > 10) {
                $this->teleport($this->getTargetPlayer()->getPosition());
                $this->speed = 0.7;
            }
        }
        if ($this->getTargetPlayer() === null or $this->getTargetPlayer()->getWorld() !== $this->getWorld()) {
            $this->flagForDespawn();
            return false;
        } else {
            if ($this->tick % 10 === 0) {
                $this->attackTargetPlayer();
            }
            if (!$this->isSprinting()) {
                $this->setSprinting(true);
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

    private function recentlyHit(): bool
    {
        return Server::getInstance()->getTick() - $this->hitTick <= 4;
    }

    private function attackTargetPlayer(): void
    {
        if (mt_rand(0, 100) % 4 === 0) {
            $this->lookAt($this->getTargetPlayer()->getPosition()->asVector3());
        }
        if ($this->isLookingAt($this->getTargetPlayer()->getPosition()->asVector3())) {
            if ($this->getLocation()->distance($this->getTargetPlayer()->getPosition()->asVector3()) <= 2.3) {
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
        return 1 && abs($expectedYaw - $this->getLocation()->getYaw()) <= 10;
    }

    public function attack(EntityDamageEvent $source): void
    {
        parent::attack($source);
        $this->hitTick = 20;
        $entity = $source->getEntity();
        if ($entity instanceof Player) {
            if ($entity->getName() !== $this->target) {
                $source->cancel();
                $entity->sendMessage(Loader::getPrefixCore() . "You can't attack this bot!");
            }
        }
    }

    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void
    {
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
            $motion->x += $x * $f * $this->hKb;
            $motion->y += $this->yKb;
            $motion->z += $z * $f * $this->hKb;
            if ($motion->y > $this->yKb) {
                $motion->y = $this->yKb;
            }
            $this->setMotion($motion);
        }
        parent::knockBack($this->hKb, $this->yKb, 0.35);
        $this->hitTick = Server::getInstance()->getTick();
    }
}