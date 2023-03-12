<?php

declare(strict_types=1);

namespace Nayuki\Game\Kits;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

final class Sumo extends Kit
{
    /**
     * @return Item[]
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
     * @return Item[]
     */
    public function getInventoryItems(): array
    {
        $contents = [];
        $contents[] = VanillaItems::STEAK()->setCount(16);
        return $contents;
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
