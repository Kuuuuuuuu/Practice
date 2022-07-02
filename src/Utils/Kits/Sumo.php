<?php
declare(strict_types=1);

namespace Kuu\Utils\Kits;

use pocketmine\item\VanillaItems;

class Sumo extends KitManager
{

    public function getArmorItems(): array
    {
        return [
            VanillaItems::AIR(),
            VanillaItems::AIR(),
            VanillaItems::AIR(),
            VanillaItems::AIR()
        ];
    }

    public function getInventoryItems(): array
    {
        return [VanillaItems::AIR()];
    }
}