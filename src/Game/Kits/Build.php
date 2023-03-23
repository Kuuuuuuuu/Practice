<?php

declare(strict_types=1);

namespace Nayuki\Game\Kits;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

final class Build extends Kit
{
    /**
     * @return Item[]
     */
    public function getArmorItems(): array
    {
        return [
            VanillaItems::IRON_HELMET()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)),
            VanillaItems::IRON_CHESTPLATE()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)),
            VanillaItems::IRON_LEGGINGS()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)),
            VanillaItems::IRON_BOOTS()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1))
        ];
    }

    /**
     * @return Item[]
     */
    public function getInventoryItems(): array
    {
        return [
            VanillaItems::IRON_SWORD()->setUnbreakable(),
            VanillaItems::ENDER_PEARL()->setCount(16),
            VanillaItems::GOLDEN_APPLE()->setCount(3),
            VanillaBlocks::SANDSTONE()->asItem()->setCount(64),
            VanillaBlocks::SANDSTONE()->asItem()->setCount(64)
        ];
    }

    /**
     * @param Player $player
     * @return void
     */
    public function setEffect(Player $player): void
    {
    }
}
