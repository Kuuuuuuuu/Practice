<?php

declare(strict_types=1);

namespace Kohaku\Core\Entity;

use JsonException;
use Kohaku\Core\Loader;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;

class KillLeaderboard extends Human
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
            $tops = Loader::getInstance()->KillLeaderboard;
            if (count($tops) > 0) {
                arsort($tops);
                $i = 1;
                foreach ($tops as $name => $wins) {
                    $subtitle .= " §7[§b# " . $i . "§7]. §f" . $name . "§7: §f" . $wins . "§e Kills\n";
                    if ($i >= 10) {
                        break;
                    }
                    ++$i;
                }
            }
            $this->setNameTag("§bMost Kills Players\n" . $subtitle);
        }
        $this->setNameTagAlwaysVisible(true);
        return parent::onUpdate($currentTick);
    }
}