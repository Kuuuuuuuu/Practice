<?php

declare(strict_types=1);

namespace Kuu\Utils;

use DateTime;
use Exception;
use JsonException;
use Kuu\Loader;
use Kuu\NeptunePlayer;
use Kuu\Utils\DiscordUtils\DiscordWebhook;
use Kuu\Utils\DiscordUtils\DiscordWebhookEmbed;
use Kuu\Utils\DiscordUtils\DiscordWebhookUtils;
use Kuu\Utils\Forms\CustomForm;
use Kuu\Utils\Forms\SimpleForm;
use Kuu\Utils\Kits\KitRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Skin;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class FormUtils
{

    private array $players = [];

    public function Form1($player): void
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    Loader::getArenaManager()->onJoinBoxing($player);
                    break;
                case 1:
                    Loader::getArenaManager()->onJoinFist($player);
                    break;
                case 2:
                    Loader::getArenaManager()->onJoinCombo($player);
                    break;
                case 3:
                    Loader::getArenaManager()->onJoinKnockback($player);
                    break;
                case 4:
                    Loader::getArenaManager()->onJoinResistance($player);
                    break;
                case 5:
                    $this->formkit($player);
                    break;
                case 6:
                    Loader::getArenaManager()->onJoinOITC($player);
                    break;
                case 7:
                    Loader::getArenaManager()->onJoinBuild($player);
                    break;
                default:
                    print 'Error';
            }
            return true;
        });

        $form->setTitle('§dNeptune §cMenu');
        $form->addButton("§aBoxing\n§dPlayers: §f" . Loader::getArenaFactory()->getPlayers(Loader::getArenaFactory()->getBoxingArena()), 0, 'textures/items/diamond_sword.png');
        $form->addButton("§aFist\n§dPlayers: §f" . Loader::getArenaFactory()->getPlayers(Loader::getArenaFactory()->getFistArena()), 0, 'textures/items/beef_cooked.png');
        $form->addButton("§aCombo\n§dPlayers: §f" . Loader::getArenaFactory()->getPlayers(Loader::getArenaFactory()->getComboArena()), 0, 'textures/items/apple_golden.png');
        $form->addButton("§aKnockback\n§dPlayers: §f" . Loader::getArenaFactory()->getPlayers(Loader::getArenaFactory()->getKnockbackArena()), 0, 'textures/items/stick.png');
        $form->addButton("§aResistance\n§dPlayers: §f" . Loader::getArenaFactory()->getPlayers(Loader::getArenaFactory()->getResistanceArena()), 0, 'textures/ui/resistance_effect.png');
        $form->addButton("§aKitPVP\n§dPlayers: §f" . Loader::getArenaFactory()->getPlayers(Loader::getArenaFactory()->getKitPVPArena()), 0, 'textures/ui/recipe_book_icon.png');
        $form->addButton("§aOITC\n§dPlayers: §f" . Loader::getArenaFactory()->getPlayers(Loader::getArenaFactory()->getOITCArena()), 0, 'textures/items/bow_standby.png');
        $form->addButton("§aBuild\n§dPlayers: §f" . Loader::getArenaFactory()->getPlayers(Loader::getArenaFactory()->getBuildArena()), 0, 'textures/items/diamond_pickaxe.png');
        $player->sendForm($form);
    }

    private function formkit(Player $player): void
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
        $form->setTitle('§dNeptune §eKitPVP');
        $form->setContent('§eNow Playing: §a' . Loader::getArenaFactory()->getPlayers(Loader::getArenaFactory()->getKitPVPArena()));
        $form->addButton('§aAssasins');
        $form->addButton('§aTank');
        $form->addButton('§aBoxing');
        $form->addButton('§aBower');
        $form->addButton('§aReaper');
        $player->sendForm($form);
    }

    /**
     * @throws Exception
     */
    private function assasins(Player $player): void
    {
        Loader::getArenaManager()->onJoinKitpvp($player);
        $item = VanillaItems::IRON_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000));
        $item2 = VanillaItems::SPIDER_EYE()->setCustomName('§r§6Teleport');
        $player->getInventory()->setItem(8, $item2);
        $player->getInventory()->setItem(0, $item);
        $player->getInventory()->addItem(VanillaItems::GOLDEN_APPLE()->setCount(3));
        $player->getArmorInventory()->setHelmet(VanillaItems::IRON_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setChestplate(VanillaItems::IRON_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setLeggings(VanillaItems::IRON_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setBoots(VanillaItems::IRON_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 9999999, 1));
    }

    /**
     * @throws Exception
     */
    private function tank(Player $player): void
    {
        Loader::getArenaManager()->onJoinKitpvp($player);
        $item = VanillaItems::DIAMOND_AXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000));
        $player->getInventory()->setItem(0, $item);
        $item2 = VanillaItems::REDSTONE_DUST()->setCustomName('§r§6Ultimate Tank');
        $player->getInventory()->setItem(8, $item2);
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), 9999999, 0));
        $player->getInventory()->addItem(VanillaItems::GOLDEN_APPLE()->setCount(3));
        $player->getArmorInventory()->setHelmet(VanillaItems::DIAMOND_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setChestplate(VanillaItems::IRON_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setLeggings(VanillaItems::IRON_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setBoots(VanillaItems::DIAMOND_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->setHealth(30);
    }

    /**
     * @throws Exception
     */
    private function boxing(Player $player): void
    {
        Loader::getArenaManager()->onJoinKitpvp($player);
        $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 9999999, 2, false));
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 9999999, 1, false));
        $item2 = VanillaItems::DIAMOND()->setCustomName('§r§6Ultimate Boxing');
        $player->getInventory()->setItem(8, $item2);
        $player->getInventory()->addItem(VanillaItems::GOLDEN_APPLE()->setCount(3));
        $player->getArmorInventory()->setLeggings(VanillaItems::CHAINMAIL_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
    }

    /**
     * @throws Exception
     */
    private function bower(Player $player): void
    {
        Loader::getArenaManager()->onJoinKitpvp($player);
        $item = VanillaItems::BOW()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY(), 1))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 4))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000));
        $player->getInventory()->setItem(0, $item);
        $item2 = VanillaItems::EMERALD()->setCustomName('§r§6Ultimate Bower');
        $player->getInventory()->setItem(8, $item2);
        $player->getInventory()->addItem(VanillaItems::GOLDEN_APPLE()->setCount(3));
        $player->getInventory()->addItem(VanillaItems::ARROW());
        $player->getArmorInventory()->setHelmet(VanillaItems::LEATHER_CAP()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setChestplate(VanillaItems::LEATHER_TUNIC()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setLeggings(VanillaItems::LEATHER_PANTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getArmorInventory()->setBoots(VanillaItems::LEATHER_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2)));
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 9999999, 2));
        $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 9999999, 3));
    }

    /**
     * @throws Exception
     */
    private function reaper(Player $player): void
    {
        Loader::getArenaManager()->onJoinKitpvp($player);
        $item = VanillaItems::DIAMOND_HOE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 4));
        $item2 = VanillaItems::WITHER_SKELETON_SKULL()->setCustomName('§r§6Reaper');
        $player->getInventory()->setItem(8, $item2);
        $player->getInventory()->addItem(VanillaItems::GOLDEN_APPLE()->setCount(3));
        $player->getInventory()->setItem(0, $item);
        $player->getArmorInventory()->setBoots(VanillaItems::DIAMOND_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)));
    }

    public function duelForm(Player $player): void
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            if ($player instanceof NeptunePlayer) {
                switch ($result) {
                    case 0:
                        $player->setCurrentKit(KitRegistry::fromString('Fist'));
                        $player->setInQueue(true);
                        $player->getInventory()->clearAll();
                        $player->checkQueue();
                        $player->getInventory()->setItem(8, VanillaItems::COMPASS()->setCustomName('§r§cLeave Queue')->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        break;
                    case 1:
                        $player->setCurrentKit(KitRegistry::fromString('NoDebuff'));
                        $player->setInQueue(true);
                        $player->getInventory()->clearAll();
                        $player->checkQueue();
                        $player->getInventory()->setItem(8, VanillaItems::COMPASS()->setCustomName('§r§cLeave Queue')->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        break;
                    case 2:
                        $player->setCurrentKit(KitRegistry::fromString('Classic'));
                        $player->setInQueue(true);
                        $player->getInventory()->clearAll();
                        $player->checkQueue();
                        $player->getInventory()->setItem(8, VanillaItems::COMPASS()->setCustomName('§r§cLeave Queue')->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        break;
                    case 3:
                        $player->setCurrentKit(KitRegistry::fromString('SG'));
                        $player->setInQueue(true);
                        $player->getInventory()->clearAll();
                        $player->checkQueue();
                        $player->getInventory()->setItem(8, VanillaItems::COMPASS()->setCustomName('§r§cLeave Queue')->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        break;
                    case 4:
                        $player->setCurrentKit(KitRegistry::fromString('BuildUHC'));
                        $player->setInQueue(true);
                        $player->getInventory()->clearAll();
                        $player->checkQueue();
                        $player->getInventory()->setItem(8, VanillaItems::COMPASS()->setCustomName('§r§cLeave Queue')->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        break;
                    case 5:
                        Loader::getArenaUtils()->JoinRandomArenaSumo($player);
                        break;
                }
            }
            return true;
        });
        $form->setTitle('§dNeptune §cDuel');
        $form->addButton("§aFist\n§dQueue§f: " . $this->getQueue('Fist'), 0, 'textures/items/paper.png');
        $form->addButton("§aNoDebuff\n§dQueue§f: " . $this->getQueue('NoDebuff'), 0, 'textures/items/paper.png');
        $form->addButton("§aClassic\n§dQueue§f: " . $this->getQueue('Classic'), 0, 'textures/items/paper.png');
        $form->addButton("§aSG\n§dQueue§f: " . $this->getQueue('SG'), 0, 'textures/items/paper.png');
        $form->addButton("§aBuildUHC\n§dQueue§f: " . $this->getQueue('BuildUHC'), 0, 'textures/items/paper.png');
        $form->addButton("§aSumo\n§dQueue§f: " . Loader::getArenaFactory()->getPlayers(Loader::getArenaFactory()->getSumoDArena()), 0, 'textures/items/paper.png');
        $player->sendForm($form);
    }

    private function getQueue(string $kit): int
    {
        $kitcount = 0;
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            if ($p instanceof NeptunePlayer) {
                try {
                    if ($p->getDuelKit()?->getName() === $kit) {
                        $kitcount++;
                    }
                } catch (Exception) {
                    $kitcount = 0;
                }
            }
        }
        return $kitcount ?? 0;
    }

    public function settingsForm($player): void
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
                case 3:
                    $this->getArtifactForm($player);
                    break;
                case 4:
                    $this->editkitform($player);
                    break;

            }
            return true;
        });
        $form->setTitle('§dNeptune §cMenu');
        $form->addButton('§aChange §dName', 0, 'textures/ui/dressing_room_skins.png');
        $form->addButton('§aReport §dPlayers', 0, 'textures/blocks/barrier.png');
        $form->addButton('§aChange §dCapes', 0, 'textures/items/snowball.png');
        $form->addButton('§aArtifacts', 0, 'textures/items/diamond_axe.png');
        $form->addButton('§aEdit §dKit', 0, 'textures/items/diamond_pickaxe.png');
        $player->sendForm($form);
    }

    public function NickForm($player): void
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
                    if (Loader::getInstance()->getArenaUtils()->getData($player->getName())->getTag() !== null) {
                        $player->setNameTag(Loader::getInstance()->getArenaUtils()->getData($player->getName())->getRank() . '§a ' . $player->getName() . ' §f[' . Loader::getInstance()->getArenaUtils()->getData($player->getName())->getTag() . '§f]');
                    } else {
                        $player->setNameTag(Loader::getInstance()->getArenaUtils()->getData($player->getName())->getRank() . '§a ' . $player->getName());
                    }
                    $player->sendMessage(Loader::getPrefixCore() . '§eYour nickname has been resetted!');
                    break;
            }
            return true;
        });
        $name = '§eNow Your Name is: §a' . $player->getDisplayName();
        $form->setTitle('§dNeptune §cNick');
        $form->setContent($name);
        $form->addButton("§aChange Name\n§r§8Tap to continue", 0, 'textures/ui/confirm');
        $form->addButton("§cReset Name\n§r§8Tap to reset", 0, 'textures/ui/trash');
        $player->sendForm($form);
    }

    public function CustomNickForm($player): void
    {
        $form = new CustomForm(function (Player $player, array $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            if (strlen($data[0]) >= 15) {
                $player->sendMessage(Loader::getPrefixCore() . '§cYour nickname is too long!');
            } elseif (Server::getInstance()->getPlayerByPrefix($data[0]) === null || $data[0] === '' || mb_strtolower($data[0]) === 'iskohakuchan') {
                $player->sendMessage(Loader::getPrefixCore() . '§cYou cant use this nickname!');
            } else {
                $player->setDisplayName($data[0]);
                if (Loader::getInstance()->getArenaUtils()->getData($player->getName())->getTag() !== null) {
                    $player->setNameTag(Loader::getInstance()->getArenaUtils()->getData($player->getName())->getRank() . '§a ' . $data[0] . ' §f[' . Loader::getInstance()->getArenaUtils()->getData($player->getName())->getTag() . '§f]');
                } else {
                    $player->setNameTag(Loader::getInstance()->getArenaUtils()->getData($player->getName())->getRank() . '§a ' . $data[0]);
                }
                $player->sendMessage(Loader::getPrefixCore() . '§6Your nickname is now §c' . $data[0]);
            }
            return true;
        });
        $form->setTitle('§dNeptune §cNick');
        $form->addInput('§eEnter New Name Here!');
        $player->sendForm($form);
    }

    public function reportForm($player): void
    {
        $list = [];
        foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $p) {
            $list[] = $p->getName();
        }
        $this->players[$player->getName()] = $list;
        $form = new CustomForm(function (Player $player, array $data = null) {
            if ($data !== null) {
                $web = new DiscordWebhook(Loader::getInstance()->getConfig()->get('api'));
                $msg = new DiscordWebhookUtils();
                $e = new DiscordWebhookEmbed();
                $index = $data[1];
                $e->setTitle('Player Report');
                $e->setFooter('Made By KohakuChan');
                $e->setTimestamp(new Datetime());
                $e->setColor(0x00ff00);
                $e->setDescription("{$player->getName()} Report {$this->players[$player->getName()][$index]}  | Reason: $data[2]");
                $msg->addEmbed($e);
                $web->send($msg);
                $player->sendMessage(Loader::getPrefixCore() . '§aReport Sent!');
                foreach (Server::getInstance()->getOnlinePlayers() as $p) {
                    if ($p->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        $p->sendMessage(Loader::getPrefixCore() . "§a{$player->getName()} §eReport §a{$this->players[$player->getName()][$index]} §e| Reason: §a$data[2]");
                    }
                }
            }
            return true;
        });
        $form->setTitle('§dNeptune §cReport');
        $form->addLabel('§aReport');
        $form->addDropdown('§eSelect a player', $this->players[$player->getName()]);
        $form->addInput('§dReason', 'Type a reason');
        $player->sendForm($form);
    }

    public function openCapesUI($player): void
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if (is_null($result)) {
                return true;
            }
            switch ($result) {
                case 0:
                    $oldSkin = $player->getSkin();
                    $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), '', $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
                    $player->setSkin($setCape);
                    $player->sendSkin();
                    if (Loader::getInstance()->CapeData->get($player->getName()) !== null) {
                        Loader::getInstance()->CapeData->remove($player->getName());
                        Loader::getInstance()->CapeData->save();
                    }
                    $player->sendMessage(Loader::getPrefixCore() . '§aCape Removed!');
                    break;
                case 1:
                    $this->openCapeListUI($player);
                    break;
            }
            return true;
        });
        $form->setTitle('§dNeptune §cCapes');
        $form->addButton('§aRemove your Cape');
        $form->addButton('§aChoose a Cape');
        $player->sendForm($form);
    }

    /**
     * @throws JsonException
     */
    public function openCapeListUI($player): void
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if (is_null($result)) {
                return true;
            }
            $cape = $data;
            if (!file_exists(Loader::getInstance()->getDataFolder() . 'cosmetic/capes/' . $data . '.png')) {
                $player->sendMessage(Loader::getPrefixCore() . '§cCape not found!');
            } else {
                $oldSkin = $player->getSkin();
                $capeData = Loader::getCosmeticHandler()->createCape($cape);
                $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
                $player->setSkin($setCape);
                $player->sendSkin();
                $msg = Loader::getPrefixCore() . '§aCape set to {name}!';
                $msg = str_replace('{name}', $cape, $msg);
                $player->sendMessage($msg);
                Loader::getInstance()->CapeData->set($player->getName(), $cape);
                Loader::getInstance()->CapeData->save();
            }
            return true;
        });
        $form->setTitle('§dNeptune §cCapes');
        foreach (Loader::getCosmeticHandler()->getCapes() as $capes) {
            $form->addButton("§a$capes", -1, '', $capes);
        }
        $player->sendForm($form);
    }

    public function getArtifactForm(Player $player): bool
    {
        $form = new SimpleForm(function (Player $event, $data = null) {
            if (($event instanceof NeptunePlayer) && $data !== null) {
                if ($data === 'None') {
                    return;
                }
                $cosmetic = Loader::getCosmeticHandler();
                if (($key = array_search($data, $cosmetic->cosmeticAvailable, true)) !== false) {
                    if (str_contains($data, 'SP-')) {
                        $event->setStuff('');
                        $cosmetic->setCostume($event, $cosmetic->cosmeticAvailable[$key]);
                    } else {
                        $event->setStuff($cosmetic->cosmeticAvailable[$key]);
                        $cosmetic->setSkin($event, $cosmetic->cosmeticAvailable[$key]);
                    }
                    $event->sendMessage(Loader::getPrefixCore() . 'Change Artifact to' . " {$cosmetic->cosmeticAvailable[$key]}.");
                }
            }
        });
        $form->setTitle('§dNeptune §cArtifact');
        /* @var NeptunePlayer $player */
        $validstuffs = $player->getValidStuffs();
        if (count($validstuffs) <= 1) {
            $form->addButton('None', -1, '', 'None');
            $player->sendForm($form);
        }
        foreach ($validstuffs as $stuff) {
            if ($stuff === 'None') {
                continue;
            }
            $form->addButton('§a' . $stuff, -1, '', $stuff);
        }
        $player->sendForm($form);
        return true;
    }

    public function editkitform($player): void
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            if ($player instanceof NeptunePlayer) {
                switch ($result) {
                    case 0:
                        $player->getInventory()->clearAll();
                        $player->getArmorInventory()->clearAll();
                        $player->setImmobile();
                        $player->setEditKit('build');
                        $player->sendMessage(Loader::getPrefixCore() . '§aEdit kit enabled');
                        $player->sendMessage(Loader::getPrefixCore() . "§aType §l§cConfirm §r§a to confirm\n§aพิมพ์ §l§cConfirm §r§a เพื่อยืนยัน");
                        $player->getInventory()->setItem(0, VanillaItems::IRON_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        $player->getInventory()->addItem(VanillaItems::GOLDEN_APPLE()->setCount(3));
                        $player->getInventory()->addItem(VanillaItems::ENDER_PEARL()->setCount(2));
                        $player->getInventory()->addItem(VanillaBlocks::WOOL()->asItem()->setCount(128));
                        $player->getInventory()->addItem(VanillaBlocks::COBWEB()->asItem());
                        $player->getInventory()->addItem(VanillaItems::SHEARS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        $player->getArmorInventory()->setHelmet(VanillaItems::IRON_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        $player->getArmorInventory()->setChestplate(VanillaItems::IRON_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        $player->getArmorInventory()->setLeggings(VanillaItems::IRON_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        $player->getArmorInventory()->setBoots(VanillaItems::IRON_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        break;
                }
            }
            return true;
        });
        $form->setTitle('§l§cEdit Kit');
        $form->setContent('§7Select a kit to edit');
        $form->addButton('§aBuild Kit');
        $player->sendForm($form);
    }

    public function botForm($player): void
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    /* @var $player NeptunePlayer */
                    $player->queueBotDuel();
                    break;
            }
            return true;
        });
        $form->setTitle('§dNeptune §cMenu');
        $form->setContent('§dPlayers: §e' . $this->getQueueBot());
        $form->addButton('§aFist §dBot', 0, 'textures/items/diamond.png');
        $player->sendForm($form);
    }

    private function getQueueBot(): int
    {
        $count = 0;
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (($player instanceof NeptunePlayer) && $player->isDueling() && str_contains($player->getWorld()->getFolderName(), 'bot')) {
                $count++;
            }
        }
        return $count;
    }

    public function partyForm(NeptunePlayer $player): void
    {
        $form = new SimpleForm(function (NeptunePlayer $player, $data = null): void {
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 'create':
                    if ($player->isInParty()) {
                        $player->sendMessage(Loader::getPrefixCore() . '§cYou are already in a party.');
                    } else if ($player->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                        $player->sendMessage(Loader::getPrefixCore() . '§cYou cannot create a party here.');
                    }
                    $this->createParty($player);
                    break;
                case 'invites':
                    $this->invitesForm($player);
                    break;
                case 'list':
                    $this->partiesForm($player);
                    break;
                case 'duel':
                    break;
                case 'members':
                    $this->partyMembersForm($player);
                    break;
                case 'leave':
                    $player->getParty()?->removeMember($player);
                    break;
                case 'invite':
                    $this->playerListForm($player);
                    break;
                case 'manage':
                    $this->partyManageForm($player);
                    break;
                case 'disband':
                    $player->getParty()?->disband();
                    break;
            }
        });
        $form->setTitle('§dNeptune §cParty');
        $party = $player->getParty();
        if (!$player->isInParty()) {
            $form->addButton('§dCreate', -1, '', 'create');
            $form->addButton('§dInvites', -1, '', 'invites');
        }
        if ($player->isInParty()) {
            $form->addButton('§dMembers', -1, '', 'members');
            if ($party !== null) {
                if (!$party->isLeader($player)) {
                    $form->addButton('§dLeave', -1, '', 'leave');
                }
                if ($party->isLeader($player)) {
                    $form->addButton('§dDuel', -1, '', 'duel');
                    $form->addButton('§dInvite', -1, '', 'invite');
                    $form->addButton('§dManage', -1, '', 'manage');
                    $form->addButton('§dDisband', -1, '', 'disband');
                }
            }
        }
        $form->addButton('§dList', -1, '', 'list');
        $player->sendForm($form);
    }

    public function createParty(NeptunePlayer $player): void
    {
        $form = new CustomForm(function (NeptunePlayer $player, array $data = null): void {
            if ($data === null) {
                return;
            }
            if ($data[0] === null || $data[0] === '' || strlen($data[0]) >= 15) {
                $player->sendMessage(Loader::getPrefixCore() . '§cInvalid party name.');
                return;
            }
            $name = $data[0];
            PartyManager::createParty($player, $name, (int)$data[1]);
        });
        $form->setTitle('§dNeptune §cParty');
        $form->addInput('§aParty Name');
        $form->addSlider('§aParty Size', 2, 100, 1);
        $player->sendForm($form);
    }

    public function invitesForm(NeptunePlayer $player): void
    {
        $form = new SimpleForm(function (NeptunePlayer $player, $data = null): void {
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 'exit':
                    $this->partyForm($player);
                    unset(Loader::getInstance()->TargetInvites[$player->getName()]);
                    break;
                default:
                    Loader::getInstance()->TargetInvites[$player->getName()] = $data;
                    if ($player->isInParty()) {
                        $player->sendMessage(Loader::getPrefixCore() . '§cYou are already in a party.');
                    } else {
                        $this->manageInviteForm($player);
                    }
                    break;
            }
        });
        $form->setTitle('§dNeptune §cInvites');
        foreach (PartyManager::getInvites($player) as $invite) {
            $party = $invite->getParty()->getName();
            $form->addButton($party, -1, '', $invite->getParty()->getName());
        }
        $form->addButton('§dBack', -1, '', 'exit');
        $player->sendForm($form);
    }

    public function manageInviteForm(NeptunePlayer $player): void
    {
        $form = new SimpleForm(function (NeptunePlayer $player, $data = null): void {
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 'exit':
                    $this->invitesForm($player);
                    break;
                case 'accept':
                    $invite = PartyManager::getInvite(Loader::getInstance()->TargetInvites[$player->getName()]);
                    $party = $invite->getParty();
                    if ($party->isFull()) {
                        $player->sendMessage(Loader::getPrefixCore() . '§cThat party is full.');
                    } else {
                        $invite->accept();
                    }
                    break;
                case 'decline':
                    $invite = PartyManager::getInvite(Loader::getInstance()->TargetInvites[$player->getName()]);
                    $invite->decline();
                    break;
            }
            unset(Loader::getInstance()->TargetPlayer[$player->getName()]);
        });
        $form->setTitle('§dNeptune §cInvitation');
        $form->addButton('§aAccept', -1, '', 'accept');
        $form->addButton('§cDecline', -1, '', 'decline');
        $form->addButton('§dBack', -1, '', 'exit');
        $player->sendForm($form);
    }

    public function partiesForm(NeptunePlayer $player): void
    {
        $form = new SimpleForm(function (NeptunePlayer $player, $data = null): void {
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 'exit':
                    $this->partyForm($player);
                    break;
                default:
                    Loader::getInstance()->TargetParty[$player->getName()] = $data;
                    $p = Loader::getInstance()->TargetParty[$player->getName()];
                    $party = PartyManager::getParty($p);
                    if ($party === null) {
                        return;
                    }
                    if ($player->isInParty()) {
                        $player->sendMessage(Loader::getPrefixCore() . '§cYou are already in a party.');
                    } else if (!PartyManager::doesPartyExist($party)) {
                        $player->sendMessage(Loader::getPrefixCore() . '§cThat party does not exist.');
                    } else if ($party->isClosed()) {
                        $player->sendMessage(Loader::getPrefixCore() . '§cThat party is closed.');
                    } else if ($party->isFull()) {
                        $player->sendMessage(Loader::getPrefixCore() . '§cThat party is full.');
                    } else {
                        $party->addMember($player);
                    }
                    break;
            }
            unset(Loader::getInstance()->TargetParty[$player->getName()]);
        });
        $form->setTitle('§dNeptune §cParties');
        foreach (Loader::getInstance()->PartyData as $party) {
            $name = $party->getName();
            $members = count($party->getMembers());
            $capacity = $party->getCapacity();
            $form->addButton($name . ' (' . $members . '/' . $capacity . ')', -1, '', $party->getName());
        }
        $form->addButton('§dBack', -1, '', 'exit');
        $player->sendForm($form);
    }

    public function partyMembersForm(NeptunePlayer $player): void
    {
        $form = new SimpleForm(function (NeptunePlayer $player, $data = null): void {
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 'exit':
                    $this->partyForm($player);
                    unset(Loader::getInstance()->TargetPlayer[$player->getName()]);
                    break;
                default:
                    $party = $player->getParty();
                    if (!$party?->isLeader($player)) {
                        $player->sendMessage(Loader::getPrefixCore() . '§cYou cannot manage party members.');
                    } else if ($player->getName() === $data) {
                        $player->sendMessage(Loader::getPrefixCore() . '§cYou cannot manage yourself.');
                    }
                    Loader::getInstance()->TargetPlayer[$player->getName()] = $data;
                    $this->managePartyMemberForm($player);
                    break;
            }
        });
        $party = $player->getParty();
        $members = count($party->getMembers());
        $capacity = $party->getCapacity();
        $form->setTitle('§6Members (' . $members . '/' . $capacity . ')');
        foreach ($party->getMembers() as $members) {
            $players = Server::getInstance()->getPlayerExact($members->getName());
            /* @var NeptunePlayer $players */
            $form->addButton($members->getName() . "\n" . $players?->getPartyRank(), -1, '', $members->getName());
        }
        $form->addButton('§dBack', -1, '', 'exit');
        $player->sendForm($form);
    }

    public function managePartyMemberForm(NeptunePlayer $player): void
    {
        $form = new SimpleForm(function (NeptunePlayer $player, $data = null): void {
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 'exit':
                    $this->partyMembersForm($player);
                    break;
                case 'kick':
                    $party = $player->getParty();
                    $target = Server::getInstance()->getPlayerExact(Loader::getInstance()->TargetPlayer[$player->getName()]);
                    if ($target instanceof Player) {
                        $party?->kickMember($target);
                    }
                    break;
            }
            unset(Loader::getInstance()->TargetPlayer[$player->getName()]);
        });
        $form->setTitle('§dNeptune §cManage ' . Loader::getInstance()->TargetPlayer[$player->getName()]);
        $form->addButton('§cKick', -1, '', 'kick');
        $form->addButton('§dBack', -1, '', 'exit');
        $player->sendForm($form);
    }

    public function playerListForm(NeptunePlayer $player): void
    {
        $form = new SimpleForm(function (NeptunePlayer $player, $data = null): void {
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 'exit':
                    $this->partyForm($player);
                    break;
                default:
                    Loader::getInstance()->TargetPlayer[$player->getName()] = $data;
                    $target = Server::getInstance()->getPlayerExact(Loader::getInstance()->TargetPlayer[$player->getName()]);
                    /* @var NeptunePlayer $target */
                    $party = $player->getParty();
                    if ($target === null) {
                        $player->sendMessage(Loader::getPrefixCore() . '§cThis player is offline.');
                    } else if ($target->getName() === $player->getName()) {
                        $player->sendMessage(Loader::getPrefixCore() . '§cYou cannot invite yourself.');
                    } else if (PartyManager::hasInvite($target, $party ?? null)) {
                        $player->sendMessage(Loader::getPrefixCore() . '§cThis player has already been invited to your party.');
                    } else if ($target->isInParty()) {
                        $player->sendMessage(Loader::getPrefixCore() . '§cThis player is already in a party.');
                    } else {
                        PartyManager::invitePlayer($party ?? null, $player, $target);
                    }
                    break;
            }
            unset(Loader::getInstance()->TargetPlayer[$player->getName()]);
        });
        $form->setTitle('§aOnline Players');
        foreach (Server::getInstance()->getOnlinePlayers() as $players) {
            $form->addButton($players->getDisplayName(), -1, '', $players->getName());
        }
        $form->addButton('§dBack', -1, '', 'exit');
        $player->sendForm($form);
    }

    public function partyManageForm(NeptunePlayer $player): void
    {
        $form = new CustomForm(function (NeptunePlayer $player, $data = null): void {
            if ($data === null) {
                return;
            }
            if ($data === 0) {
                return;
            }
            switch ($data[0]) {
                case 0:
                    $party = $player->getParty();
                    if (!$party?->isClosed()) {
                        return;
                    }
                    $party?->setOpen();
                    $player->sendMessage(Loader::getPrefixCore() . '§aYour party is now open, players can join.');
                    break;
                case 1:
                    $party = $player->getParty();
                    if ($party?->isClosed()) {
                        return;
                    }
                    $party?->setClosed();
                    $player->sendMessage(Loader::getPrefixCore() . '§aparty is now closed, players can only join via invitation.');
                    break;
            }
        });
        $form->setTitle('§dNeptune §cManage Party');
        if ($player->getParty()?->isClosed()) {
            $form->addToggle('§cClosed', true);
        } else {
            $form->addToggle('§aOpen', false);
        }
        $player->sendForm($form);
    }

    public function ProfileForm(NeptunePlayer $player, ?NeptunePlayer $player2): void
    {
        $form = new CustomForm(static function (Player $player, $data) {
        });

        if ($player2 !== null) {
            $data = Loader::getArenaUtils()->getData($player2->getName());
            $name = $player2->getName();
        } else {
            $data = Loader::getArenaUtils()->getData($player->getName());
            $name = $player->getName();
        }
        $form->setTitle("$name's §cProfile");
        $form->addLabel(
            '§aKills§f: §e' . $data->getKills() .
            "\n§e" .
            "\n§aDeath§f: §e" . $data->getDeaths() .
            "\n§e" .
            "\n§aRank§f: §e" . $data->getRank() .
            "\n§e" .
            "\n§aKDR§f: §e" . $data->getKDR() .
            "\n§e" .
            "\n§aElo§f: §e" . $data->getElo()
        );
        $player->sendForm($form);
    }
}