<?php

declare(strict_types=1);

namespace Nayuki\Entities;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

final class JoinEntity extends Living
{

    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $nbt);
        $this->setNameTagAlwaysVisible();
        $this->setScale(1.5);
        $this->setNameTag("§ePractice\n§7Click to Play");
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::ZOMBIE;
    }

    /**
     * @param CompoundTag $nbt
     * @return void
     */
    public function initEntity(CompoundTag $nbt): void
    {
        $this->setHealth(20);
        $this->setMaxHealth(20);
        parent::initEntity($nbt);
    }

    public function attack(EntityDamageEvent $source): void
    {
        $source->cancel();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return '';
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(1.8, 0.6); //TODO: eye height ??
    }
}
