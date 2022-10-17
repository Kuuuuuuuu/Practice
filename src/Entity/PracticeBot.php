<?php

namespace Kuu\Entity;

use Exception;
use Kuu\PracticeConfig;
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

class PracticeBot extends Human
{

    public int $pearlcooldown = 0;
    private string $target;
    private int $mode;
    private float $speed = 0.85;
    private int $enderpearl = 16;
    private int $pots = 33;

    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null, string $target = '', ?int $mode = PracticeConfig::BOT_FIST)
    {
        parent::__construct($location, $skin, $nbt);
        $this->target = $target;
        $this->alwaysShowNameTag = true;
        $this->gravityEnabled = true;
        $this->gravity = 0.08;
        $this->mode = $mode;
        if ($mode === PracticeConfig::BOT_NODEBUFF) {
            $sword = VanillaItems::DIAMOND_SWORD();
            $this->getInventory()->setItem(0, $sword);
            $this->getArmorInventory()->setHelmet(VanillaItems::DIAMOND_HELMET());
            $this->getArmorInventory()->setChestplate(VanillaItems::DIAMOND_CHESTPLATE());
            $this->getArmorInventory()->setLeggings(VanillaItems::DIAMOND_LEGGINGS());
            $this->getArmorInventory()->setBoots(VanillaItems::DIAMOND_BOOTS());
            $this->getInventory()->setHeldItemIndex(0);
        }
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
        if ($this->mode === PracticeConfig::BOT_NODEBUFF) {
            if (($this->enderpearl !== 0) && $this->pearlcooldown === 0) {
                if ($this->getTargetPlayer()->getPosition()->distance($this->getLocation()) > 20) {
                    $this->pearl();
                } elseif ($this->getTargetPlayer()->getHealth() < 3) {
                    $this->pearl();
                    $this->jump();
                }
            }
            if ($this->getHealth() < 5) {
                $this->pot();
            }
        }
        $this->location->yaw = rad2deg(atan2(-$x, $z));
        $this->location->pitch = rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));
        $health = round($this->getHealth());
        $this->setNameTag('§bPracticeBot ' . "\n" . TextFormat::RED . $health);
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
            $this->teleport($this->getTargetPlayer()?->getPosition()->asVector3()->subtract(1, 0, 1));
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
        $xDist = $target->getX() - $this->getLocation()->getX();
        $zDist = $target->getZ() - $this->getLocation()->getZ();
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

    public function getName(): string
    {
        return 'PracticeBot';
    }
}