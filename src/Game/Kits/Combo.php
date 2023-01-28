<?php

declare(strict_types=1);

namespace Nayuki\Game\Kits;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

final class Combo extends Kit
{
    /**
     * @return array<Item>
     */
    public function getArmorItems(): array
    {
        return [
            VanillaItems::DIAMOND_HELMET()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)),
            VanillaItems::DIAMOND_CHESTPLATE()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)),
            VanillaItems::DIAMOND_LEGGINGS()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)),
            VanillaItems::DIAMOND_BOOTS()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1))
        ];
    }

    /**
     * @return array<Item>
     */
    public function getInventoryItems(): array
    {
        $contents = [];
        $contents[] = VanillaItems::DIAMOND_SWORD()->setUnbreakable()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2));
        $contents[] = VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(16);
        return $contents;
    }

    /**
     * @param Player $player
     * @return void
     */
    public function setEffect(Player $player): void
    {
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 10000000, 1, false));
    }
}
