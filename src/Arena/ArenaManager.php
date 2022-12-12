<?php

declare(strict_types=1);

namespace Nayuki\Arena;

use Nayuki\PracticeCore;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;

class ArenaManager
{
    /**
     * @param Player $player
     * @return void
     */
    public function onJoinBoxing(Player $player): void
    {
        if (PracticeCore::getArenaFactory()->getBoxingArena() == null) {
            $player->sendMessage(PracticeCore::getPrefixCore() . '§cArena is not set!');
        } else {
            $world = Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBoxingArena());
            if ($world instanceof World) {
                $player->getInventory()->clearAll();
                $player->getArmorInventory()->clearAll();
                $player->setHealth($player->getMaxHealth());
                $player->getEffects()->clear();
                $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 99999, 10, false));
                $player->teleport($world->getSafeSpawn());
                $player->teleport(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY() + 3, $player->getPosition()->getZ()));
                PracticeCore::getScoreboardManager()->setBoxingScoreboard($player);
            }
        }
    }

    /**
     * @param Player $player
     * @return void
     */
    public function onJoinNodebuff(Player $player): void
    {
        if (PracticeCore::getArenaFactory()->getNodebuffArena() == null) {
            $player->sendMessage(PracticeCore::getPrefixCore() . '§cArena is not set!');
        } else {
            $world = Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getNodebuffArena());
            if ($world instanceof World) {
                $player->getInventory()->clearAll();
                $player->setHealth($player->getMaxHealth());
                $player->getArmorInventory()->clearAll();
                $player->getEffects()->clear();
                $player->teleport($world->getSafeSpawn());
                $this->getKitNodebuff($player);
                PracticeCore::getScoreboardManager()->setArenaScoreboard($player);
            }
        }
    }

    /**
     * @param Player $player
     * @return void
     */
    public function getKitNodebuff(Player $player): void
    {
        $inventory = $player->getInventory();
        $armorInventory = $player->getArmorInventory();
        $helmet = VanillaItems::DIAMOND_HELMET();
        $chestplate = VanillaItems::DIAMOND_CHESTPLATE();
        $leggings = VanillaItems::DIAMOND_LEGGINGS();
        $boots = VanillaItems::DIAMOND_BOOTS();
        $sword = VanillaItems::DIAMOND_SWORD();
        foreach ([$helmet, $chestplate, $leggings, $boots] as $item) {
            $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
            $item->setUnbreakable();
        }
        $armorInventory->setHelmet($helmet);
        $armorInventory->setBoots($boots);
        $armorInventory->setChestplate($chestplate);
        $armorInventory->setLeggings($leggings);
        $sword->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2))->setUnbreakable();
        $inventory->addItem($sword);
        $inventory->addItem(VanillaItems::ENDER_PEARL()->setCount(16));
        $inventory->addItem(VanillaItems::STRONG_HEALING_SPLASH_POTION()->setCount(34));
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 99999, 1, false));
    }
}
