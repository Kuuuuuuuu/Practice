<?php

declare(strict_types=1);

namespace Kohaku\Entity;

use JsonException;
use Kohaku\Loader;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;

class ParkourLeaderboard extends Human
{
    private int $tick = 0;

    /**
     * @throws JsonException
     */
    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $skin, $nbt);
        $this->skin = new Skin('Standard_Custom', str_repeat("\x00", 8192), '', 'geometry.humanoid.custom');
        $this->sendSkin();
        $this->forceMovementUpdate = false;
        $this->gravity = 0;
        $this->setScale(0.1);
        $this->getNetworkProperties()->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0);
        $this->getNetworkProperties()->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0);
    }

    public function attack(EntityDamageEvent $source): void
    {
        $source->cancel();
    }

    public function onUpdate(int $currentTick): bool
    {
        $this->tick++;
        if ($this->tick % 25 === 0) {
            $subtitle = "";
            $tops = Loader::getInstance()->ParkourLeaderboard;
            if (count($tops) > 0) {
                asort($tops);
                $i = 1;
                foreach ($tops as $name => $wins) {
                    $subtitle .= " §7[§d# " . $i . "§7]. §f" . $name . "§7: §f" . $wins . "§e MS\n";
                    if ($i >= 10) {
                        break;
                    }
                    ++$i;
                }
            }
            $this->setNameTag("§dMost Fastest Parkour Players\n" . $subtitle);
        }
        $this->setNameTagAlwaysVisible(true);
        return parent::onUpdate($currentTick);
    }
}