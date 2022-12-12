<?php

declare(strict_types=1);

namespace Nayuki\Game\Kits;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

class Boxing extends Kit
{
    /**
     * @return array<Item>
     */
    public function getArmorItems(): array
    {
        return [
            VanillaItems::AIR(),
            VanillaItems::AIR(),
            VanillaItems::AIR(),
            VanillaItems::AIR()
        ];
    }

    /**
     * @return array<Item>
     */
    public function getInventoryItems(): array
    {
        return [
            VanillaItems::DIAMOND_SWORD()->setUnbreakable()
        ];
    }

    /**
     * @param Player $player
     * @return void
     */
    public function setEffect(Player $player): void
    {
        $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 10000000, 255, false));
    }
}
