<?php

namespace Kuu\Entity;

use Exception;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\sound\EndermanTeleportSound;

class NeptuneBot extends Human
{

    private string $target;
    private string $mode;
    private float $speed = 0.85;
    private int $enderpearl = 16;
    private int $pearltime = 0;
    private int $pots = 33;
    public int $pearlcooldown = 0;

    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null, string $target = '', ?string $mode = '')
    {
        parent::__construct($location, $skin, $nbt);
        $this->target = $target;
        $this->alwaysShowNameTag = true;
        $this->gravityEnabled = true;
        $this->gravity = 0.077;
        $this->mode = $mode;
        if ($mode === 'NoDebuff') {
            $this->giveItems();
        }
    }

    private function giveItems(): void
    {
        $sword = VanillaItems::DIAMOND_SWORD();
        $this->getInventory()->setItem(0, $sword);
        $this->getArmorInventory()->setHelmet(VanillaItems::DIAMOND_HELMET());
        $this->getArmorInventory()->setChestplate(VanillaItems::DIAMOND_CHESTPLATE());
        $this->getArmorInventory()->setLeggings(VanillaItems::DIAMOND_LEGGINGS());
        $this->getArmorInventory()->setBoots(VanillaItems::DIAMOND_BOOTS());
        $this->getInventory()->setHeldItemIndex(0);
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
        $this->getInventory()->setHeldItemIndex(0);
        $position = $this->getTargetPlayer()->getPosition()->asVector3();
        $x = $position->x - $this->getLocation()->getX();
        $z = $position->z - $this->getLocation()->getZ();
        $y = $position->y - $this->getLocation()->getY();
        if ($this->getTargetPlayer()->isSprinting()) {
            $this->motion->x = $this->getSpeed() * 0.4 * ($x / (abs($x) + abs($z)));
            $this->motion->z = $this->getSpeed() * 0.4 * ($z / (abs($x) + abs($z)));
        } else {
            $this->motion->x = $this->getSpeed() * 0.35 * ($x / (abs($x) + abs($z)));
            $this->motion->z = $this->getSpeed() * 0.35 * ($z / (abs($x) + abs($z)));
        }
        if ($this->mode === 'NoDebuff') {
            if ($this->enderpearl !== 0) {
                if ($this->pearlcooldown === 0) {
                    if (($this->getTargetPlayer()->getPosition()->distance($this->getLocation()) > 10) && $this->getHealth() > 7) {
                        $this->pearltime++;
                        if ($this->pearltime >= 7) {
                            $this->pearltime = 0;
                            $this->pearl();
                        }
                    }
                    if ($this->getHealth() < 5) {
                        $x = $this->getTargetPlayer()->getPosition()->getX() - random_int(15, 30);
                        $z = $this->getTargetPlayer()->getPosition()->getZ() - random_int(15, 30);
                        $this->getWorld()->addParticle($origin = $this->getPosition(), new EndermanTeleportParticle());
                        $this->getWorld()->addSound($origin, new EndermanTeleportSound());
                        $this->teleport(new Vector3($x, $this->getLocation()->getY(), $z));
                    }
                    if ($this->getTargetPlayer()->getHealth() < 3) {
                        $this->pearltime++;
                        if ($this->pearltime >= 7) {
                            $this->pearltime = 0;
                            $this->pearl();
                        }
                        $this->jump();
                    }
                }
            }
            if ($this->getHealth() < 5) {
                $this->pot();
            }
        }
        $this->location->yaw = rad2deg(atan2(-$x, $z));
        $this->location->pitch = rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));
        $health = round($this->getHealth());
        $this->setNameTag(TextFormat::BOLD . '§dFistBot ' . "\n" . TextFormat::RED . $health);
        $this->attackTargetPlayer();
        if (!$this->isSprinting()) {
            $this->setSprinting();
        }
        return $this->isAlive();
    }

    private function getTargetPlayer(): ?Player
    {
        return Server::getInstance()->getPlayerByPrefix($this->target);
    }

    private function getSpeed(): float
    {
        return $this->speed;
    }

    /**
     * @throws Exception
     */
    private function pearl(): void
    {
        if ($this->enderpearl > 0) {
            $this->enderpearl--;
            $this->getWorld()->addParticle($origin = $this->getPosition(), new EndermanTeleportParticle());
            $this->getWorld()->addSound($origin, new EndermanTeleportSound());
            $this->teleport($this->getTargetPlayer()?->getPosition()->asVector3()->subtract(random_int(0, 10), 0, random_int(6, 15)));
            $this->pearlcooldown = 10;
        }
    }

    private function pot(): void
    {
        if ($this->getLocation()->getYaw() < 0) {
            $this->getLocation()->yaw = abs($this->getLocation()->getYaw());
        } elseif ($this->getLocation()->getYaw() === 0) {
            $this->getLocation()->yaw = -180;
        } else {
            $this->getLocation()->yaw = -$this->getLocation()->getYaw();
        }
        $this->getLocation()->pitch = 85;
        $this->getInventory()->setHeldItemIndex(2);
        $player = $this->getTargetPlayer();
        $soundPacket = new LevelSoundEventPacket();
        $soundPacket->sound = LevelSoundEvent::GLASS;
        $soundPacket->position = $this->getPosition()->asVector3();
        $player?->getNetworkSession()->sendDataPacket($soundPacket);
        $effect = new EffectInstance(VanillaEffects::INSTANT_HEALTH(), 0, 1);
        $this->getEffects()->add($effect);
        if ($this->getPosition()->distance($player?->getPosition()->asVector3()) <= 2) {
            $player?->getEffects()->add($effect);
        }
        $this->pots--;
    }

    private function attackTargetPlayer(): void
    {
        if ($this->isLookingAt($this->getTargetPlayer()?->getPosition()->asVector3()) && $this->getLocation()->distance($this->getTargetPlayer()?->getPosition()->asVector3()) <= 3.2) {
            $this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
            $event = new EntityDamageByEntityEvent($this, $this->getTargetPlayer(), EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getInventory()->getItemInHand()->getAttackPoints());
            $this->broadcastMotion();
            $this->getTargetPlayer()?->attack($event);
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
        return 5.2 && abs($expectedYaw - $this->getLocation()->getYaw()) <= 10;
    }

    public function attack(EntityDamageEvent $source): void
    {
        parent::attack($source);
        if ($source->isCancelled()) {
            $source->cancel();
        } elseif ($source instanceof EntityDamageByEntityEvent) {
            $killer = $source->getDamager();
            if ($killer instanceof Player) {
                $deltaX = $this->getPosition()->getX() - $killer->getPosition()->getX();
                $deltaZ = $this->getPosition()->getZ() - $killer->getPosition()->getZ();
                $this->knockBack($deltaX, $deltaZ);
            }
        }
    }

    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void
    {
        $xzKB = 0.309;
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
            $motion->x *= 0.65;
            $motion->y *= 1.25;
            $motion->z *= 0.65;
            if ($this->isAlive() && !$this->isClosed()) {
                $this->move(0, 0, 0);
                $this->setMotion($motion);
            }
        }
    }

    public function getName(): string
    {
        return 'NeptuneBot';
    }
}