<?php

declare(strict_types=1);

namespace Nayuki\Game\Kits;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

final class Fist extends Kit
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
    }
}
