<?php

namespace Nayuki\Items;

use Nayuki\PracticeConfig;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\PotionType;
use pocketmine\item\ProjectileItem;
use pocketmine\player\Player;

class CustomSplashPotion extends ProjectileItem
{
    /** @var PotionType */
    private PotionType $potionType;

    public function __construct(PotionType $potionType)
    {
        parent::__construct(new ItemIdentifier(ItemIds::SPLASH_POTION, PotionTypeIdMap::getInstance()->toId($potionType)), $potionType->getDisplayName());
        $this->potionType = $potionType;
    }

    /**
     * @return PotionType
     */
    public function getType(): PotionType
    {
        return $this->potionType;
    }

    /**
     * @return int
     */
    public function getMaxStackSize(): int
    {
        return 1;
    }

    /**
     * @return float
     */
    public function getThrowForce(): float
    {
        return PracticeConfig::SplashForce;
    }

    /**
     * @param Location $location
     * @param Player $thrower
     * @return Throwable
     */
    public function createEntity(Location $location, Player $thrower): Throwable
    {
        $potion = new SplashPotion(Location::fromObject($thrower->getEyePos(), $thrower->getWorld(), $thrower->getLocation()->yaw, $thrower->getLocation()->pitch), $thrower, $this->potionType);
        $potion->setMotion($thrower->getDirectionVector()->multiply(0.5));
        return $potion;
    }
}
