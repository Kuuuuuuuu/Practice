<?php
declare(strict_types=1);

namespace Kohaku\Core\Utils\Kits;

use pocketmine\item\VanillaItems;

class Fist extends KitManager
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
        return [
            VanillaItems::STEAK()->setCount(64)
        ];
    }
}