<?php

declare(strict_types=1);

namespace Kohaku\Core\Utils;

use DateTime;
use Kohaku\Core\Loader;
use Kohaku\Core\Utils\DiscordUtils\DiscordWebhook;
use Kohaku\Core\Utils\DiscordUtils\DiscordWebhookEmbed;
use Kohaku\Core\Utils\DiscordUtils\DiscordWebhookUtils;
use Kohaku\Core\Utils\Forms\CustomForm;
use Kohaku\Core\Utils\Forms\SimpleForm;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Skin;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;

class FormUtils
{

    public array $players = [];
    public int $count = 0;

    public function Form1($player)
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    Loader::$arena->onJoinParkour($player);
                    break;
                case 1:
                    Loader::$arena->onJoinBoxing($player);
                    break;
                case 2:
                    Loader::$arena->onJoinFist($player);
                    break;
                case 3:
                    Loader::$arena->onJoinCombo($player);
                    break;
                case 4:
                    Loader::$arena->onJoinKnockback($player);
                    break;
                case 5:
                    Loader::$arena->onJoinResistance($player);
                    break;
                case 6:
                    $this->formkit($player);
                    break;
                case 7:
                    Loader::$arena->onJoinOITC($player);
                    break;
                default:
                    print "Error";
            }
            return true;
        });

        $form->setTitle("§bHorizon §eMenu");
        $form->addButton("§aParkour\n§bPlayers: §f" . Loader::$arenafac->getPlayers(Loader::$arenafac->getParkourArena() ?? null) ?? 0, 0, "textures/items/name_tag.png");
        $form->addButton("§aBoxing\n§bPlayers: §f" . Loader::$arenafac->getPlayers(Loader::$arenafac->getBoxingArena() ?? null) ?? 0, 0, "textures/items/diamond_sword.png");
        $form->addButton("§aFist\n§bPlayers: §f" . Loader::$arenafac->getPlayers(Loader::$arenafac->getFistArena() ?? null) ?? 0, 0, "textures/items/snowball.png");
        $form->addButton("§aCombo\n§bPlayers: §f" . Loader::$arenafac->getPlayers(Loader::$arenafac->getComboArena() ?? null) ?? 0, 0, "textures/items/apple.png");
        $form->addButton("§aKnockback\n§bPlayers: §f" . Loader::$arenafac->getPlayers(Loader::$arenafac->getKnockbackArena() ?? null) ?? 0, 0, "textures/items/stick.png");
        $form->addButton("§aResistance\n§bPlayers: §f" . Loader::$arenafac->getPlayers(Loader::$arenafac->getResistanceArena() ?? null) ?? 0, 0, "textures/items/diamond_pickaxe.png");
        $form->addButton("§aKitPVP\n§bPlayers: §f" . Loader::$arenafac->getPlayers(Loader::$arenafac->getKitPVPArena() ?? null) ?? 0, 0, "textures/items/diamond_axe.png");
        $form->addButton("§aOITC\n§bPlayers: §f" . Loader::$arenafac->getPlayers(Loader::$arenafac->getOITCArena() ?? null) ?? 0, 0, "textures/items/bow.png");
        $player->sendForm($form);
    }

    private function formkit(Player $player)
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    $this->assasins($player);
                    break;
                case 1:
                    $this->tank($player);
                    break;
                case 2:
                    $this->boxing($player);
                    break;
                case 3:
                    $this->bower($player);
                    break;
                case 4;
                    $this->reaper($player);
                    break;
            }
            return true;
        });
        $form->setTitle("§bHorizon §eKitPVP");
        $form->setContent("§eNow Playing: §a" . Loader::$arenafac->getPlayers(Loader::$arenafac->getKitPVPArena()));
        $form->addButton("§eAssasins");
        $form->addButton("§eTank");
        $form->addButton("§eBoxing");
        $form->addButton("§eBower");
        $form->addButton("§eReaper");
        $player->sendForm($form);
    }

    private function assasins(Player $player)
    {
        Loader::$arena->onJoinKitpvp($player);
        $item = ItemFactory::getInstance()->get(ItemIds::IRON_SWORD, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000));
        $item2 = ItemFactory::getInstance()->get(ItemIds::ENDER_EYE, 0, 1)->setCustomName("§r§6Teleport");
        $player->getInventory()->setItem(8, $item2);
        $player->getInventory()->setItem(0, $item);
        $player->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::GOLDEN_APPLE, 0, 3));
        $player->getArmorInventory()->setHelmet(ItemFactory::getInstance()->get(ItemIds::IRON_HELMET, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setChestplate(ItemFactory::getInstance()->get(ItemIds::IRON_CHESTPLATE, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setLeggings(ItemFactory::getInstance()->get(ItemIds::IRON_LEGGINGS, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setBoots(ItemFactory::getInstance()->get(ItemIds::IRON_BOOTS, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 9999999, 1));
    }

    private function tank(Player $player)
    {
        Loader::$arena->onJoinKitpvp($player);
        $item = ItemFactory::getInstance()->get(ItemIds::DIAMOND_AXE, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000));
        $player->getInventory()->setItem(0, $item);
        $item2 = ItemFactory::getInstance()->get(ItemIds::REDSTONE, 0, 1)->setCustomName("§r§6Ultimate Tank");
        $player->getInventory()->setItem(8, $item2);
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), 9999999, 0));
        $player->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::GOLDEN_APPLE, 0, 3));
        $player->getArmorInventory()->setHelmet(ItemFactory::getInstance()->get(ItemIds::DIAMOND_HELMET, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setChestplate(ItemFactory::getInstance()->get(ItemIds::IRON_CHESTPLATE, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setLeggings(ItemFactory::getInstance()->get(ItemIds::IRON_LEGGINGS, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setBoots(ItemFactory::getInstance()->get(ItemIds::DIAMOND_BOOTS, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->setHealth(30);
    }

    private function boxing(Player $player)
    {
        Loader::$arena->onJoinKitpvp($player);
        $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 9999999, 2, false));
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 9999999, 1, false));
        $item2 = ItemFactory::getInstance()->get(ItemIds::DIAMOND, 0, 1)->setCustomName("§r§6Ultimate Boxing");
        $player->getInventory()->setItem(8, $item2);
        $player->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::GOLDEN_APPLE, 0, 3));
        $player->getArmorInventory()->setLeggings(ItemFactory::getInstance()->get(ItemIds::CHAIN_LEGGINGS, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
    }

    private function bower(Player $player)
    {
        Loader::$arena->onJoinKitpvp($player);
        $item = ItemFactory::getInstance()->get(ItemIds::BOW, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY(), 1))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 4))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000));
        $player->getInventory()->setItem(0, $item);
        $item2 = ItemFactory::getInstance()->get(ItemIds::EMERALD, 0, 1)->setCustomName("§r§6Ultimate Bower");
        $player->getInventory()->setItem(8, $item2);
        $player->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::GOLDEN_APPLE, 0, 3));
        $player->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::ARROW, 0, 1));
        $player->getArmorInventory()->setHelmet(ItemFactory::getInstance()->get(ItemIds::LEATHER_HELMET, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setChestplate(ItemFactory::getInstance()->get(ItemIds::LEATHER_CHESTPLATE, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setLeggings(ItemFactory::getInstance()->get(ItemIds::LEATHER_LEGGINGS, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setBoots(ItemFactory::getInstance()->get(ItemIds::LEATHER_BOOTS, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 9999999, 2));
        $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 9999999, 3));
    }

    private function reaper(Player $player)
    {
        Loader::$arena->onJoinKitpvp($player);
        $item = ItemFactory::getInstance()->get(ItemIds::DIAMOND_HOE, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 4));
        $item2 = ItemFactory::getInstance()->get(ItemIds::SKULL, 0, 1)->setCustomName("§r§6Reaper");
        $player->getInventory()->setItem(8, $item2);
        $player->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::GOLDEN_APPLE, 0, 3));
        $player->getInventory()->setItem(0, $item);
        $player->getArmorInventory()->setBoots(ItemFactory::getInstance()->get(ItemIds::DIAMOND_BOOTS, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)));
    }

    public function settingsForm($player)
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    $this->NickForm($player);
                    break;
                case 1:
                    $this->reportForm($player);
                    break;
                case 2:
                    $this->openCapesUI($player);
                    break;
            }
            return true;
        });
        $form->setTitle("§bHorizon §eMenu");
        $form->addButton("§bChange §aName", 0, "textures/ui/dressing_room_skins.png");
        $form->addButton("§bReport §aPlayers", 0, "textures/blocks/barrier.png");
        $form->addButton("§bChange §aCapes", 0, "textures/items/snowball.png");
        $player->sendForm($form);
    }

    public function NickForm($player)
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    $this->CustomNickForm($player);
                    break;
                case 1:
                    $player->setDisplayName($player->getName());
                    $player->setNameTag($player->getName());
                    $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§eYour nickname has been resetted!");
                    break;
            }
            return true;
        });
        $name = "§eNow Your Name is: §a" . $player->getDisplayName();
        $form->setTitle("§bHorizon §eNick");
        $form->setContent($name);
        $form->addButton("§a§lChange Name\n§r§8Tap to continue", 0, "textures/ui/confirm");
        $form->addButton("§c§lReset Name\n§r§8Tap to reset", 0, "textures/ui/trash");
        $player->sendForm($form);
    }

    public function CustomNickForm($player)
    {
        $form = new CustomForm(function (Player $player, array $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            $player->setDisplayName($data[0]);
            $player->setNameTag($data[0]);
            $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§6Your nickname is now §c" . $data[0]);
            return true;
        });
        $form->setTitle("§bHorizon §eNick");
        $form->addInput("§eEnter New Name Here!");
        $player->sendForm($form);
    }

    public function reportForm($player)
    {
        $list = [];
        foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $p) {
            $list[] = $p->getName();
        }
        $this->players[$player->getName()] = $list;
        $form = new CustomForm(function (Player $player, array $data = null) {
            if ($data === null) {
                $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§cReport submit Failed");
                return true;
            }
            $web = new DiscordWebhook(Loader::getInstance()->getConfig()->get("api"));
            $msg = new DiscordWebhookUtils();
            $e = new DiscordWebhookEmbed();
            $index = $data[1];
            $e->setTitle("Player Report");
            $e->setFooter("Made By KohakuChan");
            $e->setTimestamp(new Datetime());
            $e->setColor(0x00ff00);
            $e->setDescription("{$player->getName()} Report {$this->players[$player->getName()][$index]}  | Reason: $data[2]");
            $msg->addEmbed($e);
            $web->send($msg);
            $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§aReport was submitted!");
            return true;
        });
        $form->setTitle("§bHorizon §eReport");
        $form->addLabel("§aReport");
        $form->addDropdown("§eSelect a player", $this->players[$player->getName()]);
        $form->addInput("§bReason", "Type a reason");
        $player->sendForm($form);
    }

    public function openCapesUI($player)
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if (is_null($result)) {
                return true;
            }
            switch ($result) {
                case 0:
                    $oldSkin = $player->getSkin();
                    $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), "", $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
                    $player->setSkin($setCape);
                    $player->sendSkin();
                    if (Loader::getInstance()->CapeData->get($player->getName()) !== null) {
                        Loader::getInstance()->CapeData->remove($player->getName());
                        Loader::getInstance()->CapeData->save();
                    }
                    $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§aCape Removed!");
                    break;
                case 1:
                    $this->openCapeListUI($player);
                    break;
            }
            return true;
        });
        $form->setTitle("§bHorizon §eCapes");
        $form->addButton("§0Remove your Cape", 1);
        $form->addButton("§eChoose a Cape", 2);
        $player->sendForm($form);
    }

    public function openCapeListUI($player)
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if (is_null($result)) {
                return true;
            }
            $cape = $data;
            if (!file_exists(Loader::getInstance()->getDataFolder() . "capes/" . $data . ".png")) {
                $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§cCape not found!");
            } else {
                $oldSkin = $player->getSkin();
                $capeData = CapeUtils::getInstance()->createCape($cape);
                $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
                $player->setSkin($setCape);
                $player->sendSkin();
                $msg = Loader::getInstance()->getPrefixCore() . "§aCape set to {name}!";
                $msg = str_replace("{name}", $cape, $msg);
                $player->sendMessage($msg);
                Loader::getInstance()->CapeData->set($player->getName(), $cape);
                Loader::getInstance()->CapeData->save();
            }
            return true;
        });
        $form->setTitle("§bHorizon §eCapes");
        foreach (CapeUtils::getInstance()->getCapes() as $capes) {
            $form->addButton("$capes", -1, "", $capes);
        }
        $player->sendForm($form);
    }
}