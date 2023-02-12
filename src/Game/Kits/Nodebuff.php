<?php

declare(strict_types=1);

namespace Nayuki\Game\Kits;

use Nayuki\Items\EnderPearl;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

final class Nodebuff extends Kit
{
    /**
     * @return array<Item>
     */
    public function getArmorItems(): array
    {
        return [
            VanillaItems::DIAMOND_HELMET()->setUnbreakable(),
            VanillaItems::DIAMOND_CHESTPLATE()->setUnbreakable(),
            VanillaItems::DIAMOND_LEGGINGS()->setUnbreakable(),
            VanillaItems::DIAMOND_BOOTS()->setUnbreakable()
        ];
    }

    /**
     * @return array<Item>
     */
    public function getInventoryItems(): array
    {
        $contents = [];
        $contents[] = VanillaItems::DIAMOND_SWORD()->setUnbreakable();
        $contents[] = ItemFactory::getInstance()->get(ItemIds::ENDER_PEARL, 0, 16);
        for ($i = 0; $i < 34; $i++) {
            $contents[] = VanillaItems::STRONG_HEALING_SPLASH_POTION();
        }
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
