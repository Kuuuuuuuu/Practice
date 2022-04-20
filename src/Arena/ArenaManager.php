<?php

/** @noinspection PhpVoidFunctionResultUsedInspection */

declare(strict_types=1);

namespace Kohaku\Arena;

use Kohaku\Loader;
use Kohaku\NeptunePlayer;
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
        if (Loader::getArenaFactory()->getBoxingArena() == null) {
            $player->sendMessage(Loader::getPrefixCore() . '§cArena is not set!');
            return;
        }
        Server::getInstance()->getWorldManager()->loadWorld(Loader::getArenaFactory()->getBoxingArena());
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->setHealth(20);
        $player->getEffects()->clear();
        $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 99999, 10, false));
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBoxingArena())->getSafeSpawn());
        $player->teleport(new Vector3($player->getPosition()->asPosition()->x, $player->getPosition()->asPosition()->y + 3, $player->getPosition()->asPosition()->z));
        $pos = $player->getPosition();
        Loader::getInstance()->getArenaUtils()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
    }

    public function onJoinFist(Player $player): void
    {
        if (Loader::getArenaFactory()->getFistArena() == null) {
            $player->sendMessage(Loader::getPrefixCore() . '§cArena is not set!');
            return;
        }
        Server::getInstance()->getWorldManager()->loadWorld(Loader::getArenaFactory()->getFistArena());
        $player->getInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);
        $player->getArmorInventory()->clearAll();
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getFistArena())->getSafeSpawn());
        $player->teleport(new Vector3($player->getPosition()->asPosition()->x, $player->getPosition()->asPosition()->y + 3, $player->getPosition()->asPosition()->z));
        $pos = $player->getPosition();
        Loader::getInstance()->getArenaUtils()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
    }

    public function onJoinCombo(Player $player): void
    {
        if (Loader::getArenaFactory()->getFistArena() == null) {
            $player->sendMessage(Loader::getPrefixCore() . '§cArena is not set!');
            return;
        }
        Server::getInstance()->getWorldManager()->loadWorld(Loader::getArenaFactory()->getComboArena());
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);
        $item = VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(3);
        $player->getInventory()->addItem($item);
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getComboArena())->getSafeSpawn());
        $player->teleport(new Vector3($player->getPosition()->asPosition()->x, $player->getPosition()->asPosition()->y + 3, $player->getPosition()->asPosition()->z));
        $pos = $player->getPosition();
        Loader::getInstance()->getArenaUtils()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
    }

    public function onJoinKnockback(Player $player): void
    {
        if (Loader::getArenaFactory()->getKnockbackArena() == null) {
            $player->sendMessage(Loader::getPrefixCore() . '§cArena is not set!');
            return;
        }
        Server::getInstance()->getWorldManager()->loadWorld(Loader::getArenaFactory()->getKnockbackArena());
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);
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
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getKnockbackArena())->getSafeSpawn());
        $player->teleport(new Vector3($player->getPosition()->asPosition()->x, $player->getPosition()->asPosition()->y + 3, $player->getPosition()->asPosition()->z));
        $pos = $player->getPosition();
        Loader::getInstance()->getArenaUtils()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
    }

    public function onJoinKitpvp(Player $player): void
    {
        if (Loader::getArenaFactory()->getKitPVPArena() == null) {
            $player->sendMessage(Loader::getPrefixCore() . '§cArena is not set!');
            return;
        }
        Server::getInstance()->getWorldManager()->loadWorld(Loader::getArenaFactory()->getKitPVPArena());
        $player->getInventory()->clearAll();
        $player->setHealth(20);
        $player->getEffects()->clear();
        $player->getArmorInventory()->clearAll();
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getKitPVPArena())->getSafeSpawn());
        Loader::getInstance()->getArenaUtils()->randomSpawn($player);
        $pos = $player->getPosition();
        Loader::getInstance()->getArenaUtils()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
    }

    public function onJoinOITC(Player $player): void
    {
        if (Loader::getArenaFactory()->getOITCArena() == null) {
            $player->sendMessage(Loader::getPrefixCore() . '§cArena is not set!');
            return;
        }
        $random = Loader::getArenaFactory()->getRandomSpawnOitc();
        $player->getInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);
        $player->getArmorInventory()->clearAll();
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getOITCArena())->getSafeSpawn());
        $player->teleport(new Vector3($random['x'], $random['y'], $random['z']));
        $pos = $player->getPosition();
        Loader::getInstance()->getArenaUtils()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
        $player->getInventory()->setItem(0, VanillaItems::STONE_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1)));
        $player->getOffHandInventory()->setItem(0, VanillaItems::ARROW());
        $player->getInventory()->setItem(1, VanillaItems::BOW()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 200)));
    }

    public function onJoinResistance(Player $player): void
    {
        if (Loader::getArenaFactory()->getResistanceArena() == null) {
            $player->sendMessage(Loader::getPrefixCore() . '§cArena is not set!');
            return;
        }
        Server::getInstance()->getWorldManager()->loadWorld(Loader::getArenaFactory()->getResistanceArena());
        $player->getInventory()->clearAll();
        $player->setHealth(20);
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getResistanceArena())->getSafeSpawn());
        $pos = $player->getPosition();
        Loader::getInstance()->getArenaUtils()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
        $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 99999, 10, false));
    }

    public function onJoinBuild(Player $player): void
    {
        if (Loader::getArenaFactory()->getBuildArena() == null) {
            $player->sendMessage(Loader::getPrefixCore() . '§cArena is not set!');
            return;
        }
        $random = Loader::getArenaFactory()->getRandomSpawnBuild();
        Server::getInstance()->getWorldManager()->loadWorld(Loader::getArenaFactory()->getBuildArena());
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);
        try {
            if ($player instanceof NeptunePlayer) {
                foreach (Loader::getInstance()->KitData->get($player->getName()) as $slot => $item) {
                    $player->getInventory()->setItem($slot, Item::jsonDeserialize($item));
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
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBuildArena())->getSafeSpawn());
        $player->teleport(new Vector3($random['x'], $random['y'], $random['z']));
        $pos = $player->getPosition();
        Loader::getInstance()->getArenaUtils()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
        $player->setGamemode(GameMode::SURVIVAL());
    }
}