<?php

/** @noinspection PhpVoidFunctionResultUsedInspection */

declare(strict_types=1);

namespace Kohaku\Core\Arena;

use Exception;
use Kohaku\Core\HorizonPlayer;
use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ArenaUtils;
use Kohaku\Core\Utils\ScoreboardUtils;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;

class ArenaManager
{

    public function onJoinParkour(Player $player)
    {
        if (Loader::$arenafac->getParkourArena() == null) {
            $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
            return;
        }
        Server::getInstance()->getWorldManager()->loadWorld(Loader::$arenafac->getParkourArena());
        ScoreboardUtils::getInstance()->sb2($player);
        $item2 = ItemFactory::getInstance()->get(345, 0, 1);
        $item3 = ItemFactory::getInstance()->get(288, 0, 1);
        $item2->setCustomName("§r§aStop Timer §f| §bClick to use");
        $item3->setCustomName("§r§aBack to Checkpoint §f| §bClick to use");
        $player->getInventory()->clearAll();
        $player->getEffects()->clear();
        $player->getArmorInventory()->clearAll();
        $player->setHealth(20);
        $player->getInventory()->setItem(0, $item2);
        $player->getInventory()->setItem(8, $item3);
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())->getSafeSpawn());
        $player->teleport(new Vector3($player->getPosition()->asPosition()->x, $player->getPosition()->asPosition()->y + 10, $player->getPosition()->asPosition()->z));
        $pos = $player->getPosition();
        ArenaUtils::getInstance()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
    }

    public function onJoinBoxing(Player $player)
    {
        if (Loader::$arenafac->getBoxingArena() == null) {
            $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
            return;
        }
        Server::getInstance()->getWorldManager()->loadWorld(Loader::$arenafac->getBoxingArena());
        ScoreboardUtils::getInstance()->sb2($player);
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->setHealth(20);
        $player->getEffects()->clear();
        $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 99999, 10, false));
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBoxingArena())->getSafeSpawn());
        $player->teleport(new Vector3($player->getPosition()->asPosition()->x, $player->getPosition()->asPosition()->y + 3, $player->getPosition()->asPosition()->z));
        $pos = $player->getPosition();
        ArenaUtils::getInstance()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
    }

    public function onJoinFist(Player $player)
    {
        if (Loader::$arenafac->getFistArena() == null) {
            $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
            return;
        }
        Server::getInstance()->getWorldManager()->loadWorld(Loader::$arenafac->getFistArena());
        ScoreboardUtils::getInstance()->sb2($player);
        $player->getInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);
        $player->getArmorInventory()->clearAll();
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getFistArena())->getSafeSpawn());
        $player->teleport(new Vector3($player->getPosition()->asPosition()->x, $player->getPosition()->asPosition()->y + 3, $player->getPosition()->asPosition()->z));
        $pos = $player->getPosition();
        ArenaUtils::getInstance()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
    }

    public function onJoinCombo(Player $player)
    {
        if (Loader::$arenafac->getFistArena() == null) {
            $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
            return;
        }
        Server::getInstance()->getWorldManager()->loadWorld(Loader::$arenafac->getComboArena());
        ScoreboardUtils::getInstance()->sb2($player);
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);
        $item = ItemFactory::getInstance()->get(466, 0, 3);
        $player->getInventory()->addItem($item);
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getComboArena())->getSafeSpawn());
        $player->teleport(new Vector3($player->getPosition()->asPosition()->x, $player->getPosition()->asPosition()->y + 3, $player->getPosition()->asPosition()->z));
        $pos = $player->getPosition();
        ArenaUtils::getInstance()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
    }

    public function onJoinKnockback(Player $player)
    {
        if (Loader::$arenafac->getKnockbackArena() == null) {
            $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
            return;
        }
        Server::getInstance()->getWorldManager()->loadWorld(Loader::$arenafac->getKnockbackArena());
        ScoreboardUtils::getInstance()->sb2($player);
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 99999, 3, false));
        $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 99999, 4, false));
        $arrow = ItemFactory::getInstance()->get(262, 0, 1);
        $leap = ItemFactory::getInstance()->get(288, 0, 1);
        $leap->setCustomName("§r§eLeap§r");
        $bow = ItemFactory::getInstance()->get(261, 0, 1);
        $bow->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY(), 1));
        $bow->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000));
        $bow->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH(), 6));
        $stick = ItemFactory::getInstance()->get(280, 0, 1);
        $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 99999, 3, false));
        $stick->addEnchantment(new EnchantmentInstance(VanillaEnchantments::KNOCKBACK(), 6));
        $player->getInventory()->setItem(0, $stick);
        $player->getInventory()->setItem(12, $arrow);
        $player->getInventory()->addItem($bow);
        $player->getInventory()->addItem($leap);
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKnockbackArena())->getSafeSpawn());
        $player->teleport(new Vector3($player->getPosition()->asPosition()->x, $player->getPosition()->asPosition()->y + 3, $player->getPosition()->asPosition()->z));
        $pos = $player->getPosition();
        ArenaUtils::getInstance()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
    }

    public function onJoinKitpvp(Player $player)
    {
        if (Loader::$arenafac->getKitPVPArena() == null) {
            $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
            return;
        }
        Server::getInstance()->getWorldManager()->loadWorld(Loader::$arenafac->getKitPVPArena());
        ScoreboardUtils::getInstance()->sb2($player);
        $player->getInventory()->clearAll();
        $player->setHealth(20);
        $player->getEffects()->clear();
        $player->getArmorInventory()->clearAll();
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena())->getSafeSpawn());
        ArenaUtils::getInstance()->randomSpawn($player);
        $pos = $player->getPosition();
        ArenaUtils::getInstance()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
    }

    public function onJoinOITC(Player $player)
    {
        if (Loader::$arenafac->getOITCArena() == null) {
            $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
            return;
        }
        $random = Loader::$arenafac->getRandomSpawnOitc();
        ScoreboardUtils::getInstance()->sb2($player);
        $player->getInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);
        $player->getArmorInventory()->clearAll();
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getOITCArena())->getSafeSpawn());
        $player->teleport(new Vector3($random["x"], $random["y"], $random["z"]));
        $pos = $player->getPosition();
        ArenaUtils::getInstance()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
        if ($player instanceof HorizonPlayer) {
            try {
                $player->getInventory()->setItem(0, ItemFactory::getInstance()->get($player->getKit()["0"]["oitc"]["0"]["item"], 0, $player->getKit()["0"]["oitc"]["0"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 200)));
                $player->getInventory()->setItem(1, ItemFactory::getInstance()->get($player->getKit()["0"]["oitc"]["1"]["item"], 0, $player->getKit()["0"]["oitc"]["1"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 200)));
                $player->getInventory()->setItem(2, ItemFactory::getInstance()->get($player->getKit()["0"]["oitc"]["2"]["item"], 0, $player->getKit()["0"]["oitc"]["2"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 200)));
                $player->getInventory()->setItem(3, ItemFactory::getInstance()->get($player->getKit()["0"]["oitc"]["3"]["item"], 0, $player->getKit()["0"]["oitc"]["3"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 200)));
                $player->getInventory()->setItem(4, ItemFactory::getInstance()->get($player->getKit()["0"]["oitc"]["4"]["item"], 0, $player->getKit()["0"]["oitc"]["4"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 200)));
                $player->getInventory()->setItem(5, ItemFactory::getInstance()->get($player->getKit()["0"]["oitc"]["5"]["item"], 0, $player->getKit()["0"]["oitc"]["5"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 200)));
                $player->getInventory()->setItem(6, ItemFactory::getInstance()->get($player->getKit()["0"]["oitc"]["6"]["item"], 0, $player->getKit()["0"]["oitc"]["6"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 200)));
                $player->getInventory()->setItem(7, ItemFactory::getInstance()->get($player->getKit()["0"]["oitc"]["7"]["item"], 0, $player->getKit()["0"]["oitc"]["7"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 200)));
                $player->getInventory()->setItem(8, ItemFactory::getInstance()->get($player->getKit()["0"]["oitc"]["8"]["item"], 0, $player->getKit()["0"]["oitc"]["8"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 200)));
            } catch (Exception) {
                $player->getInventory()->setItem(1, ItemFactory::getInstance()->get(ItemIds::IRON_SWORD, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 200)));
                $player->getInventory()->setItem(19, ItemFactory::getInstance()->get(ItemIds::ARROW, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 200)));
                $player->getInventory()->setItem(0, ItemFactory::getInstance()->get(ItemIds::BOW, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 200)));
            }
        }
    }

    public function onJoinResistance(Player $player)
    {
        if (Loader::$arenafac->getResistanceArena() == null) {
            return $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
        } else {
            Server::getInstance()->getWorldManager()->loadWorld(Loader::$arenafac->getResistanceArena());
            ScoreboardUtils::getInstance()->sb2($player);
            $player->getInventory()->clearAll();
            $player->setHealth(20);
            $player->getArmorInventory()->clearAll();
            $player->getEffects()->clear();
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getResistanceArena())->getSafeSpawn());
            $pos = $player->getPosition();
            ArenaUtils::getInstance()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
                $player->teleport($pos);
            });
            $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 99999, 10, false));
            return true;
        }
    }

    public function onJoinBuild(Player $player)
    {
        if (Loader::$arenafac->getBuildArena() == null) {
            $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
            return;
        }
        $random = Loader::$arenafac->getRandomSpawnBuild();
        Server::getInstance()->getWorldManager()->loadWorld(Loader::$arenafac->getBuildArena());
        ScoreboardUtils::getInstance()->sb2($player);
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);
        if ($player instanceof HorizonPlayer) {
            try {
                $player->getInventory()->setItem(0, ItemFactory::getInstance()->get($player->getKit()["0"]["build"]["0"]["item"], 0, $player->getKit()["0"]["build"]["0"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                $player->getInventory()->setItem(1, ItemFactory::getInstance()->get($player->getKit()["0"]["build"]["1"]["item"], 0, $player->getKit()["0"]["build"]["1"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                $player->getInventory()->setItem(2, ItemFactory::getInstance()->get($player->getKit()["0"]["build"]["2"]["item"], 0, $player->getKit()["0"]["build"]["2"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                $player->getInventory()->setItem(3, ItemFactory::getInstance()->get($player->getKit()["0"]["build"]["3"]["item"], 0, $player->getKit()["0"]["build"]["3"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                $player->getInventory()->setItem(4, ItemFactory::getInstance()->get($player->getKit()["0"]["build"]["4"]["item"], 0, $player->getKit()["0"]["build"]["4"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                $player->getInventory()->setItem(5, ItemFactory::getInstance()->get($player->getKit()["0"]["build"]["5"]["item"], 0, $player->getKit()["0"]["build"]["5"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                $player->getInventory()->setItem(6, ItemFactory::getInstance()->get($player->getKit()["0"]["build"]["6"]["item"], 0, $player->getKit()["0"]["build"]["6"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                $player->getInventory()->setItem(7, ItemFactory::getInstance()->get($player->getKit()["0"]["build"]["7"]["item"], 0, $player->getKit()["0"]["build"]["7"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                $player->getInventory()->setItem(8, ItemFactory::getInstance()->get($player->getKit()["0"]["build"]["8"]["item"], 0, $player->getKit()["0"]["build"]["8"]["count"])->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
            } catch (Exception) {
                $player->getInventory()->setItem(0, ItemFactory::getInstance()->get(ItemIds::IRON_SWORD, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                $player->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::GOLDEN_APPLE, 0, 3)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                $player->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::ENDER_PEARL, 0, 2)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                $player->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::WOOL, 0, 128)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                $player->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::COBWEB, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                $player->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::SHEARS, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
            }
        }
        $player->getArmorInventory()->setHelmet(ItemFactory::getInstance()->get(ItemIds::IRON_HELMET, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
        $player->getArmorInventory()->setChestplate(ItemFactory::getInstance()->get(ItemIds::IRON_CHESTPLATE, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
        $player->getArmorInventory()->setLeggings(ItemFactory::getInstance()->get(ItemIds::IRON_LEGGINGS, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
        $player->getArmorInventory()->setBoots(ItemFactory::getInstance()->get(ItemIds::IRON_BOOTS, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBuildArena())->getSafeSpawn());
        $player->teleport(new Vector3($random["x"], $random["y"], $random["z"]));
        $pos = $player->getPosition();
        ArenaUtils::getInstance()->onChunkGenerated($pos->world, intval($player->getPosition()->getX()) >> 4, intval($player->getPosition()->getZ()) >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
        $player->setGamemode(GameMode::SURVIVAL());
    }
}