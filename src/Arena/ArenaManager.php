<?php /** @noinspection PhpVoidFunctionResultUsedInspection */

declare(strict_types=1);

namespace Kohaku\Core\Arena;

use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ArenaUtils;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;

class ArenaManager
{

    public function onJoinParkour(Player $player)
    {
        $world = Loader::$arenafac->getParkourArena();
        if ($world == null) {
            return $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
        } else {
            Server::getInstance()->getWorldManager()->loadWorld(Loader::$arenafac->getParkourArena());
            $item2 = ItemFactory::getInstance()->get(345, 0, 1);
            $item3 = ItemFactory::getInstance()->get(288, 0, 1);
            $item2->setCustomName("§r§aStop Timer §f| §bClick to use");
            $item3->setCustomName("§r§aBack to Checkpoint §f| §bClick to use");
            $player->getInventory()->clearAll();
            $player->getEffects()->clear();
            $player->getArmorInventory()->clearAll();
            $player->getInventory()->setItem(0, $item2);
            $player->getInventory()->setItem(8, $item3);
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())->getSafeSpawn());
            $player->teleport(new Vector3($player->getPosition()->asPosition()->x, $player->getPosition()->asPosition()->y + 10, $player->getPosition()->asPosition()->z));
            return true;
        }
    }

    public function onJoinBoxing(Player $player)
    {
        $world = Loader::$arenafac->getBoxingArena();
        if ($world == null) {
            return $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
        } else {
            Server::getInstance()->getWorldManager()->loadWorld(Loader::$arenafac->getBoxingArena());
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->getEffects()->clear();
            $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 99999, 10, false));
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBoxingArena())->getSafeSpawn());
            $player->teleport(new Vector3($player->getPosition()->asPosition()->x, $player->getPosition()->asPosition()->y + 3, $player->getPosition()->asPosition()->z));
            return true;
        }
    }

    public function onJoinFist(Player $player)
    {
        $world = Loader::$arenafac->getFistArena();
        if ($world == null) {
            return $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
        } else {
            Server::getInstance()->getWorldManager()->loadWorld(Loader::$arenafac->getFistArena());
            $player->getInventory()->clearAll();
            $player->getEffects()->clear();
            $player->getArmorInventory()->clearAll();
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getFistArena())->getSafeSpawn());
            $player->teleport(new Vector3($player->getPosition()->asPosition()->x, $player->getPosition()->asPosition()->y + 3, $player->getPosition()->asPosition()->z));
            return true;
        }
    }

    public function onJoinCombo(Player $player)
    {
        $world = Loader::$arenafac->getFistArena();
        if ($world == null) {
            return $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
        } else {
            Server::getInstance()->getWorldManager()->loadWorld(Loader::$arenafac->getComboArena());
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->getEffects()->clear();
            $item = ItemFactory::getInstance()->get(466, 0, 3);
            $player->getInventory()->addItem($item);
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getComboArena())->getSafeSpawn());
            $player->teleport(new Vector3($player->getPosition()->asPosition()->x, $player->getPosition()->asPosition()->y + 3, $player->getPosition()->asPosition()->z));
            return true;
        }
    }

    public function onJoinKnockback(Player $player)
    {
        $world = Loader::$arenafac->getKnockbackArena();
        if ($world == null) {
            return $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
        } else {
            Server::getInstance()->getWorldManager()->loadWorld(Loader::$arenafac->getKnockbackArena());
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->getEffects()->clear();
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
            return true;
        }
    }

    public function onJoinKitpvp(Player $player)
    {
        if (Loader::$arenafac->getKitPVPArena() == null) {
            return $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
        } else {
            Server::getInstance()->getWorldManager()->loadWorld(Loader::$arenafac->getKitPVPArena());
            $player->getInventory()->clearAll();
            $player->getEffects()->clear();
            $player->getArmorInventory()->clearAll();
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena())->getSafeSpawn());
            ArenaUtils::getInstance()->randomSpawn($player);
            return true;
        }
    }

    public function onJoinOITC(Player $player)
    {
        if (Loader::$arenafac->getOITCArena() == null) {
            return $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
        } else {
            $player->getInventory()->clearAll();
            $player->getEffects()->clear();
            $player->getArmorInventory()->clearAll();
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getOITCArena())->getSafeSpawn());
            $player->getInventory()->setItem(1, ItemFactory::getInstance()->get(ItemIds::STONE_SWORD, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1)));
            $player->getInventory()->setItem(19, ItemFactory::getInstance()->get(ItemIds::ARROW, 0, 1));
            $player->getInventory()->setItem(0, ItemFactory::getInstance()->get(ItemIds::BOW, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 500))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
            return true;
        }
    }

    public function onJoinResistance(Player $player)
    {
        if (Loader::$arenafac->getKitPVPArena() == null) {
            return $player->sendMessage(Loader::getPrefixCore() . "§cArena is not set!");
        } else {
            Server::getInstance()->getWorldManager()->loadWorld(Loader::$arenafac->getResistanceArena());
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->getEffects()->clear();
            $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getResistanceArena())->getSafeSpawn());
            $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 99999, 10, false));
            return true;
        }
    }
}