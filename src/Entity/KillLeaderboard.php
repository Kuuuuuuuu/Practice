<?php

declare(strict_types=1);

namespace Kuu\Entity;

use JsonException;
use Kuu\PracticeCore;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;

class KillLeaderboard extends Human
{
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
        $subtitle = '';
        $tops = PracticeCore::getInstance()->KillLeaderboard;
        if (count($tops) > 0) {
            arsort($tops);
            $i = 1;
            foreach ($tops as $name => $wins) {
                $subtitle .= ' §7[§d# ' . $i . '§7]. §f' . $name . '§7: §f' . $wins . "§e Kills\n";
                if ($i >= 10) {
                    break;
                }
                ++$i;
            }
        }
        $this->setNameTag("§dMost Kills Players\n" . $subtitle);
        $this->setNameTagAlwaysVisible();
        return parent::onUpdate($currentTick);
    }
}