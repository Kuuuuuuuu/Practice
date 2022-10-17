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
        for ($i = 0; $i < 34; $i++) {
            $contents[] = VanillaItems::STRONG_HEALING_SPLASH_POTION();
        }
        return $contents;
    }
}