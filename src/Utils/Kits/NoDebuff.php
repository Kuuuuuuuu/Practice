<?php
declare(strict_types=1);

namespace Kuu\Utils\Kits;

use pocketmine\item\VanillaItems;

class NoDebuff extends KitManager
{

    public function getArmorItems(): array
    {
        return [
            VanillaItems::DIAMOND_HELMET(),
            VanillaItems::DIAMOND_CHESTPLATE(),
            VanillaItems::DIAMOND_LEGGINGS(),
            VanillaItems::DIAMOND_BOOTS()
        ];
    }

    public function getInventoryItems(): array
    {
        $contents = [];
        $contents[] = VanillaItems::DIAMOND_SWORD();
        $contents[] = VanillaItems::ENDER_PEARL()->setCount(16);
        $contents[] = VanillaItems::STEAK()->setCount(64);
        $contents[] = VanillaItems::SWIFTNESS_POTION()->setCount(2);
        for ($i = 0; $i < 32; $i++) {
            $contents[] = VanillaItems::STRONG_HEALING_POTION();
        }
        return $contents;
    }
}