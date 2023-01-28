<?php

declare(strict_types=1);

namespace Nayuki\Entities;

use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\data\bedrock\LegacyEntityIdToStringIdMap;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;

final class JoinEntity extends Living
{
    /** @var int */
    public const TYPE_ID = EntityLegacyIds::ZOMBIE;

    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $nbt);
        $this->setNameTagAlwaysVisible();
        $this->setCanSaveWithChunk(false);
        $this->setImmobile();
        $this->setScale(1.5);
        $this->setNameTag("§ePractice\n§7Click to Play");
    }

    /**
     * @return string
     */
    public static function getNetworkTypeId(): string
    {
        return LegacyEntityIdToStringIdMap::getInstance()->legacyToString(self::TYPE_ID) ?? '0';
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

    /**
     * @return string
     */
    public function getName(): string
    {
        return '';
    }

    /**
     * @return EntitySizeInfo
     */
    public function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(1.8, 0.6);
    }
}
