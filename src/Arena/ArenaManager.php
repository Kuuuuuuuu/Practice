<?php

/** @noinspection PhpVoidFunctionResultUsedInspection */

declare(strict_types=1);

namespace Kuu\Arena;

use Exception;
use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use Throwable;

class ArenaManager
{

    public function onJoinBoxing(Player $player): void
    {
        if (PracticeCore::getArenaFactory()->getBoxingArena() == null) {
            $player->sendMessage(PracticeCore::getPrefixCore() . '§cArena is not set!');
        } else {
            Server::getInstance()->getWorldManager()->loadWorld(PracticeCore::getArenaFactory()->getBoxingArena());
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->setHealth($player->getMaxHealth());
            $player->getEffects()->clear();
            $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 99999, 10, false));
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBoxingArena())?->getSafeSpawn());
            $player->teleport(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY() + 3, $player->getPosition()->getZ()));
            PracticeCore::getInstance()->getPracticeUtils()->ChunkLoader($player);
        }
    }

    public function onJoinFist(Player $player): void
    {
        if (PracticeCore::getArenaFactory()->getFistArena() == null) {
            $player->sendMessage(PracticeCore::getPrefixCore() . '§cArena is not set!');
        } else {
            Server::getInstance()->getWorldManager()->loadWorld(PracticeCore::getArenaFactory()->getFistArena());
            $player->getInventory()->clearAll();
            $player->getEffects()->clear();
            $player->setHealth($player->getMaxHealth());
            $player->getArmorInventory()->clearAll();
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getFistArena())?->getSafeSpawn());
            $player->teleport(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY() + 3, $player->getPosition()->getZ()));
            PracticeCore::getInstance()->getPracticeUtils()->ChunkLoader($player);
        }
    }

    public function onJoinParkour(Player $player): void
    {
        if (PracticeCore::getArenaFactory()->getParkourArena() == null) {
            $player->sendMessage(PracticeCore::getPrefixCore() . '§cArena is not set!');
        } else {
            Server::getInstance()->getWorldManager()->loadWorld(PracticeCore::getArenaFactory()->getParkourArena());
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->getEffects()->clear();
            $player->setHealth($player->getMaxHealth());
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getParkourArena())?->getSafeSpawn());
            $item1 = VanillaItems::POTATO()->setCustomName('§r§aHide Player');
            $item2 = VanillaBlocks::CHEST()->asItem()->setCustomName('§r§aStop Timer');
            $item3 = VanillaItems::APPLE()->setCustomName('§r§aBack to Checkpoint');
            $player->getInventory()->setItem(4, $item1);
            $player->getInventory()->setItem(0, $item2);
            $player->getInventory()->setItem(8, $item3);
            PracticeCore::getInstance()->getPracticeUtils()->ChunkLoader($player);
            $player->teleport(new Vector3(275, 68, 212));
        }
    }

    public function onJoinCombo(Player $player): void
    {
        if (PracticeCore::getArenaFactory()->getFistArena() == null) {
            $player->sendMessage(PracticeCore::getPrefixCore() . '§cArena is not set!');
        } else {
            Server::getInstance()->getWorldManager()->loadWorld(PracticeCore::getArenaFactory()->getComboArena());
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->getEffects()->clear();
            $player->setHealth($player->getMaxHealth());
            $player->getInventory()->addItem(VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(3));
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getComboArena())?->getSafeSpawn());
            $player->teleport(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY() + 3, $player->getPosition()->getZ()));
            PracticeCore::getInstance()->getPracticeUtils()->ChunkLoader($player);
        }
    }

    public function onJoinKnockback(Player $player): void
    {
        if (PracticeCore::getArenaFactory()->getKnockbackArena() == null) {
            $player->sendMessage(PracticeCore::getPrefixCore() . '§cArena is not set!');
        } else {
            Server::getInstance()->getWorldManager()->loadWorld(PracticeCore::getArenaFactory()->getKnockbackArena());
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->getEffects()->clear();
            $player->setHealth($player->getMaxHealth());
            $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 99999, 3, false));
            $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 99999, 4, false));
            $arrow = VanillaItems::ARROW();
            $leap = VanillaItems::FEATHER();
            $leap->setCustomName('§r§eLeap§r');
            $bow = VanillaItems::BOW();
            $bow->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY(), 1));
            $bow->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000));
            $bow->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH(), 6));
            $stick = VanillaItems::STICK();
            $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 99999, 3, false));
            $stick->addEnchantment(new EnchantmentInstance(VanillaEnchantments::KNOCKBACK(), 6));
            $player->getInventory()->setItem(0, $stick);
            $player->getInventory()->setItem(12, $arrow);
            $player->getInventory()->addItem($bow);
            $player->getInventory()->addItem($leap);
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getKnockbackArena())?->getSafeSpawn());
            $player->teleport(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY() + 3, $player->getPosition()->getZ()));
            PracticeCore::getInstance()->getPracticeUtils()->ChunkLoader($player);
        }
    }

    public function onJoinOITC(Player $player): void
    {
        if (PracticeCore::getArenaFactory()->getOITCArena() == null) {
            $player->sendMessage(PracticeCore::getPrefixCore() . '§cArena is not set!');
        } else {
            $random = PracticeCore::getArenaFactory()->getRandomSpawnOitc();
            $player->getInventory()->clearAll();
            $player->getEffects()->clear();
            $player->setHealth($player->getMaxHealth());
            $player->getArmorInventory()->clearAll();
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getOITCArena())?->getSafeSpawn());
            $player->teleport(new Vector3($random['x'], $random['y'], $random['z']));
            PracticeCore::getInstance()->getPracticeUtils()->ChunkLoader($player);
            $player->getInventory()->setItem(0, VanillaItems::STONE_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1)));
            $player->getInventory()->setItem(8, VanillaItems::ARROW());
            $player->getInventory()->setItem(1, VanillaItems::BOW()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 200)));
        }
    }

    public function onJoinResistance(Player $player): void
    {
        if (PracticeCore::getArenaFactory()->getResistanceArena() == null) {
            $player->sendMessage(PracticeCore::getPrefixCore() . '§cArena is not set!');
        } else {
            Server::getInstance()->getWorldManager()->loadWorld(PracticeCore::getArenaFactory()->getResistanceArena());
            $player->getInventory()->clearAll();
            $player->setHealth($player->getMaxHealth());
            $player->getArmorInventory()->clearAll();
            $player->getEffects()->clear();
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getResistanceArena())?->getSafeSpawn());
            PracticeCore::getInstance()->getPracticeUtils()->ChunkLoader($player);
            $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 99999, 10, false));
        }
    }

    public function onJoinBuild(Player $player): void
    {
        if (PracticeCore::getArenaFactory()->getBuildArena() == null) {
            $player->sendMessage(PracticeCore::getPrefixCore() . '§cArena is not set!');
        } else {
            $random = PracticeCore::getArenaFactory()->getRandomSpawnBuild();
            Server::getInstance()->getWorldManager()->loadWorld(PracticeCore::getArenaFactory()->getBuildArena());
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->getEffects()->clear();
            $player->setHealth($player->getMaxHealth());
            try {
                if ($player instanceof PracticePlayer) {
                    $items = PracticeCore::getInstance()->KitData->get($player->getName());
                    if (is_array($items)) {
                        foreach ($items as $slot => $item) {
                            $player->getInventory()->setItem($slot, Item::jsonDeserialize($item));
                        }
                    } else {
                        throw new Exception('Not Found Inventory');
                    }
                }
            } catch (Throwable) {
                $player->getInventory()->setItem(0, VanillaItems::IRON_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                $player->getInventory()->addItem(VanillaItems::GOLDEN_APPLE()->setCount(3));
                $player->getInventory()->addItem(VanillaItems::ENDER_PEARL()->setCount(2));
                $player->getInventory()->addItem(VanillaBlocks::WOOL()->asItem()->setCount(128));
                $player->getInventory()->addItem(VanillaBlocks::COBWEB()->asItem());
                $player->getInventory()->addItem(VanillaItems::SHEARS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
            }
            $player->getArmorInventory()->setHelmet(VanillaItems::IRON_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
            $player->getArmorInventory()->setChestplate(VanillaItems::IRON_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
            $player->getArmorInventory()->setLeggings(VanillaItems::IRON_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
            $player->getArmorInventory()->setBoots(VanillaItems::IRON_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBuildArena())?->getSafeSpawn());
            $player->teleport(new Vector3($random['x'], $random['y'], $random['z']));
            PracticeCore::getInstance()->getPracticeUtils()->ChunkLoader($player);
            $player->setGamemode(GameMode::SURVIVAL());
        }
    }
}