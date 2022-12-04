<?php

/** @noinspection PhpVoidFunctionResultUsedInspection */

declare(strict_types=1);

namespace Kuu\Arena;

use Kuu\PracticeCore;
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
                PracticeCore::getInstance()->getPracticeUtils()->ChunkLoader($player);
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
                PracticeCore::getInstance()->getPracticeUtils()->ChunkLoader($player);
                $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 99999, 10, false));
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
        $protection = VanillaEnchantments::PROTECTION();
        $unbreaking = VanillaEnchantments::UNBREAKING();
        $helmet = VanillaItems::DIAMOND_HELMET();
        $helmet->addEnchantment(new EnchantmentInstance($protection));
        $helmet->addEnchantment(new EnchantmentInstance($unbreaking));
        $chestplate = VanillaItems::DIAMOND_CHESTPLATE();
        $chestplate->addEnchantment(new EnchantmentInstance($protection));
        $chestplate->addEnchantment(new EnchantmentInstance($unbreaking));
        $leggings = VanillaItems::DIAMOND_LEGGINGS();
        $leggings->addEnchantment(new EnchantmentInstance($protection));
        $leggings->addEnchantment(new EnchantmentInstance($unbreaking));
        $boots = VanillaItems::DIAMOND_BOOTS();
        $boots->addEnchantment(new EnchantmentInstance($protection));
        $boots->addEnchantment(new EnchantmentInstance($unbreaking));
        $armorInventory->setHelmet($helmet);
        $armorInventory->setBoots($boots);
        $armorInventory->setChestplate($chestplate);
        $armorInventory->setLeggings($leggings);
        $sword = VanillaItems::DIAMOND_SWORD();
        $sword->addEnchantment(new EnchantmentInstance($unbreaking, 2));
        $inventory->addItem($sword);
        $inventory->addItem(VanillaItems::ENDER_PEARL()->setCount(16));
        $inventory->addItem(VanillaItems::STRONG_HEALING_SPLASH_POTION()->setCount(31));
        $inventory->addItem(VanillaItems::SWIFTNESS_SPLASH_POTION()->setCount(1));
    }
}
