<?php

declare(strict_types=1);

namespace Kuu\Entity;

use Kuu\PracticeCore;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;

class Hologram extends Entity
{
    /** @var int */
    private int $CountdownSeconds = 1;
    /** @var string */
    private string $type = 'Unknown';
    /** @var float */
    private float $height = 0.1;
    /** @var float */
    private float $width = 0.1;

    public function __construct(Location $location, CompoundTag $nbt)
    {
        parent::__construct($location, $nbt);
        $this->forceMovementUpdate = false;
        $this->gravity = 0;
        $this->setScale(0.1);
        $this->setNameTagAlwaysVisible();
        $this->loadFromNBT($nbt);
        $this->getNetworkProperties()->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0);
        $this->getNetworkProperties()->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0);
    }

    /**
     * @param CompoundTag $nbt
     * @return void
     */
    private function loadFromNBT(CompoundTag $nbt): void
    {
        $this->type = $nbt->getString('type');
    }

    /**
     * @return string
     */
    public static function getNetworkTypeId(): string
    {
        return EntityIds::PLAYER;
    }

    /**
     * @param int $currentTick
     * @return bool
     */
    public function onUpdate(int $currentTick): bool
    {
        if ($currentTick % 20) {
            $this->CountdownSeconds--;
            if ($this->CountdownSeconds <= 0) {
                $subtitle = $this->getSubtitleType();
                $this->setNameTag($subtitle . "\n§eUpdate In: §f" . $this->CountdownSeconds);
            }
        }
        return parent::onUpdate($currentTick);
    }

    /**
     * @return string
     */
    private function getSubtitleType(): string
    {
        $subtitle = '';
        $array = [];
        $pos = 0;
        if ($this->type === 'kills') {
            foreach (PracticeCore::getPracticeUtils()->getPlayerInSession() as $player) {
                $array[$player->getName()] = PracticeCore::getPlayerSession()::getSession($player)->getKills();
            }
            $subtitle .= "§b§lTop Kills\n";
            arsort($array);
        } elseif ($this->type === 'deaths') {
            foreach (PracticeCore::getPracticeUtils()->getPlayerInSession() as $player) {
                $array[$player->getName()] = PracticeCore::getPlayerSession()::getSession($player)->getDeaths();
            }
            $subtitle .= "§b§lTop Deaths\n";
            arsort($array);
        }
        foreach ($array as $name => $kills) {
            if ($pos > 9) {
                break;
            }
            if ($pos < 3) {
                $subtitle .= '§6[' . ($pos + 1) . '] §r§7' . $name . ' §e' . $kills . "\n";
            } else {
                $subtitle .= '§7[' . ($pos + 1) . '] §r§7' . $name . ' §8' . $kills . "\n";
            }
            $pos++;
        }
        return $subtitle;
    }

    /**
     * @return EntitySizeInfo
     */
    public function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo($this->height, $this->width);
    }

    /**
     * @return CompoundTag
     */
    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();
        $nbt->setString('type', $this->type);
        return $nbt;
    }
}
