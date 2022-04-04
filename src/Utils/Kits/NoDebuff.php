<?php
declare(strict_types=1);

namespace Kohaku\Utils\Kits;

use pocketmine\data\bedrock\PotionTypeIds;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
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
        $contents[] = ItemFactory::getInstance()->get(ItemIds::POTION, PotionTypeIds::SWIFTNESS, 2);
        for ($i = 0; $i < 32; $i++) {
            $contents[] = ItemFactory::getInstance()->get(ItemIds::SPLASH_POTION, PotionTypeIds::STRONG_HEALING, 1);
        }
        return $contents;
    }
}