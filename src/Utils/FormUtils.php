<?php

declare(strict_types=1);

namespace Kuu\Utils;

use Exception;
use JsonException;
use Kuu\Lib\FormAPI\CustomForm;
use Kuu\Lib\FormAPI\SimpleForm;
use Kuu\PracticeConfig;
use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use Kuu\Utils\Kits\KitRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Skin;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class FormUtils
{

    public function Form1($player): void
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            if ($data !== null) {
                switch ($data) {
                    case 0:
                        PracticeCore::getArenaManager()->onJoinBoxing($player);
                        break;
                    case 1:
                        PracticeCore::getArenaManager()->onJoinFist($player);
                        break;
                    case 2:
                        PracticeCore::getArenaManager()->onJoinCombo($player);
                        break;
                    case 3:
                        PracticeCore::getArenaManager()->onJoinKnockback($player);
                        break;
                    case 4:
                        PracticeCore::getArenaManager()->onJoinResistance($player);
                        break;
                    case 5:
                        PracticeCore::getArenaManager()->onJoinOITC($player);
                        break;
                    case 6:
                        PracticeCore::getArenaManager()->onJoinBuild($player);
                        break;
                    default:
                        print 'Error';
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cMenu');
        $form->addButton("§aBoxing\n§dPlayers: §f" . PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getBoxingArena()), 0, 'textures/items/diamond_sword.png');
        $form->addButton("§aFist\n§dPlayers: §f" . PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getFistArena()), 0, 'textures/items/beef_cooked.png');
        $form->addButton("§aCombo\n§dPlayers: §f" . PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getComboArena()), 0, 'textures/items/apple_golden.png');
        $form->addButton("§aKnockback\n§dPlayers: §f" . PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getKnockbackArena()), 0, 'textures/items/stick.png');
        $form->addButton("§aResistance\n§dPlayers: §f" . PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getResistanceArena()), 0, 'textures/ui/resistance_effect.png');
        $form->addButton("§aOITC\n§dPlayers: §f" . PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getOITCArena()), 0, 'textures/items/bow_standby.png');
        $form->addButton("§aBuild\n§dPlayers: §f" . PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getBuildArena()), 0, 'textures/items/diamond_pickaxe.png');
        $player->sendForm($form);
    }

    public function duelForm(Player $player): void
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            if (($data !== null) && $player instanceof PracticePlayer) {
                switch ($data) {
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
                        $player->setCurrentKit(KitRegistry::fromString('Sumo'));
                        $player->setInQueue(true);
                        $player->getInventory()->clearAll();
                        $player->checkQueue();
                        $player->getInventory()->setItem(8, VanillaItems::COMPASS()->setCustomName('§r§cLeave Queue')->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        break;
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cDuel');
        $form->addButton("§aFist\n§dQueue§f: " . $this->getQueue('Fist'), 0, 'textures/items/paper.png');
        $form->addButton("§aNoDebuff\n§dQueue§f: " . $this->getQueue('NoDebuff'), 0, 'textures/items/paper.png');
        $form->addButton("§aClassic\n§dQueue§f: " . $this->getQueue('Classic'), 0, 'textures/items/paper.png');
        $form->addButton("§aSG\n§dQueue§f: " . $this->getQueue('SG'), 0, 'textures/items/paper.png');
        $form->addButton("§aBuildUHC\n§dQueue§f: " . $this->getQueue('BuildUHC'), 0, 'textures/items/paper.png');
        $form->addButton("§aSumo\n§dQueue§f: " . $this->getQueue('Sumo'), 0, 'textures/items/paper.png');
        $player->sendForm($form);
    }

    private function getQueue(string $kit): int
    {
        $kitcount = 0;
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            if ($p instanceof PracticePlayer) {
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
            if ($data !== null) {
                switch ($data) {
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
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cMenu');
        $form->addButton('§aChange §dName', 0, 'textures/ui/dressing_room_skins.png');
        $form->addButton('§aReport §dPlayers', 0, 'textures/blocks/barrier.png');
        $form->addButton('§aChange §dCapes', 0, 'textures/items/snowball.png');
        $form->addButton('§aArtifacts', 0, 'textures/items/diamond_axe.png');
        $form->addButton('§aEdit §dKit', 0, 'textures/items/diamond_pickaxe.png');
        $player->sendForm($form);
    }

    public function NickForm($player): void
    {
        $form = new SimpleForm(function (PracticePlayer $player, int $data = null) {
            if ($data !== null) {
                switch ($data) {
                    case 0:
                        $this->CustomNickForm($player);
                        break;
                    case 1:
                        $player->setDisplayName($player->getName());
                        if ($player->getData()?->getTag() !== null) {
                            $player->setNameTag('§f[' . $player->getData()?->getTag() . '§f] §b' . $player->getName());
                        } else {
                            $player->setNameTag('§b' . $player->getName());
                        }
                        $player->sendMessage(PracticeCore::getPrefixCore() . '§eYour nickname has been resetted!');
                        break;
                }
            }
        });
        $name = '§eNow Your Name is: §a' . $player->getDisplayName();
        $form->setTitle(PracticeConfig::Server_Name . '§cNick');
        $form->setContent($name);
        $form->addButton("§aChange Name\n§r§8Tap to continue", 0, 'textures/ui/confirm');
        $form->addButton("§cReset Name\n§r§8Tap to reset", 0, 'textures/ui/trash');
        $player->sendForm($form);
    }

    public function CustomNickForm($player): void
    {
        $form = new CustomForm(function (PracticePlayer $player, array $data = null) {
            if ($data !== null) {
                if (strlen($data[0]) >= 15) {
                    $player->sendMessage(PracticeCore::getPrefixCore() . '§cYour nickname is too long!');
                } elseif (Server::getInstance()->getPlayerByPrefix($data[0]) !== null && mb_strtolower($data[0]) !== 'iskohakuchan') {
                    $player->sendMessage(PracticeCore::getPrefixCore() . '§cYou cant use this nickname!');
                } else {
                    $player->setDisplayName($data[0]);
                    if ($player->getData()?->getTag() !== null) {
                        $player->setNameTag('§f[' . $player->getData()?->getTag() . '§f] §b' . $data[0]);
                    } else {
                        $player->setNameTag('§b' . $data[0]);
                    }
                    $player->sendMessage(PracticeCore::getPrefixCore() . '§6Your nickname is now §c' . $data[0]);
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cNick');
        $form->addInput('§eEnter New Name Here!');
        $player->sendForm($form);
    }

    public function reportForm($player): void
    {
        $list = [];
        foreach (PracticeCore::getInstance()->getServer()->getOnlinePlayers() as $p) {
            $list[] = $p->getName();
        }
        $form = new CustomForm(function (Player $player, array $data = null) use ($list) {
            if ($data !== null) {
                $player->sendMessage(PracticeCore::getPrefixCore() . '§aReport Sent!');
                foreach (Server::getInstance()->getOnlinePlayers() as $p) {
                    if ($p->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        $p->sendMessage(PracticeCore::getPrefixCore() . "§a{$player->getName()} §eReport §a{$list[$data[1]]} §e| Reason: §a$data[2]");
                    }
                }
            }
            return true;
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cReport');
        $form->addLabel('§aReport');
        $form->addDropdown('§eSelect a player', $list);
        $form->addInput('§dReason', 'Type a reason');
        $player->sendForm($form);
    }

    public function openCapesUI($player): void
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data !== null) {
                switch ($data) {
                    case 0:
                        $oldSkin = $player->getSkin();
                        $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), '', $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
                        $player->setSkin($setCape);
                        $player->sendSkin();
                        if (PracticeCore::getInstance()->CapeData->get($player->getName()) !== null) {
                            PracticeCore::getInstance()->CapeData->remove($player->getName());
                            PracticeCore::getInstance()->CapeData->save();
                            /* @var $player PracticePlayer */
                            $player->LoadData(false);
                        }
                        $player->sendMessage(PracticeCore::getPrefixCore() . '§aCape Removed!');
                        break;
                    case 1:
                        $this->openCapeListUI($player);
                        break;
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cCapes');
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
            if ($data !== null) {
                if (!file_exists(PracticeCore::getInstance()->getDataFolder() . 'cosmetic/capes/' . $data . '.png')) {
                    $player->sendMessage(PracticeCore::getPrefixCore() . '§cCape not found!');
                } else {
                    $oldSkin = $player->getSkin();
                    $capeData = PracticeCore::getCosmeticHandler()->createCape($data);
                    $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
                    $player->setSkin($setCape);
                    $player->sendSkin();
                    $msg = PracticeCore::getPrefixCore() . '§aCape set to {name}!';
                    $msg = str_replace('{name}', $data, $msg);
                    $player->sendMessage($msg);
                    PracticeCore::getInstance()->CapeData->set($player->getName(), $data);
                    PracticeCore::getInstance()->CapeData->save();
                    /* @var $player PracticePlayer */
                    $player->LoadData(false);
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cCapes');
        foreach (PracticeCore::getCosmeticHandler()->getCapes() as $capes) {
            $form->addButton("§a$capes", -1, '', $capes);
        }
        $player->sendForm($form);
    }

    public function getArtifactForm(Player $player): void
    {
        $form = new SimpleForm(function (Player $event, $data = null) {
            if (($event instanceof PracticePlayer) && $data !== null) {
                if ($data === 'None') {
                    return;
                }
                $event->LoadData(false);
                $cosmetic = PracticeCore::getCosmeticHandler();
                if (($key = array_search($data, $cosmetic->cosmeticAvailable, true)) !== false) {
                    if (str_contains($data, 'SP-')) {
                        $event->setStuff('');
                        $cosmetic->setCostume($event, $cosmetic->cosmeticAvailable[$key]);
                    } else {
                        $event->setStuff($cosmetic->cosmeticAvailable[$key]);
                        $cosmetic->setSkin($event, $cosmetic->cosmeticAvailable[$key]);
                    }
                    $event->sendMessage(PracticeCore::getPrefixCore() . 'Change Artifact to' . " {$cosmetic->cosmeticAvailable[$key]}.");
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cArtifact');
        /* @var PracticePlayer $player */
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
    }

    public function editkitform($player): void
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            if (($data !== null) && $player instanceof PracticePlayer) {
                switch ($data) {
                    case 0:
                        $player->getInventory()->clearAll();
                        $player->getArmorInventory()->clearAll();
                        $player->setImmobile();
                        $player->setEditKit('build');
                        $player->sendMessage(PracticeCore::getPrefixCore() . '§aEdit kit enabled');
                        $player->sendMessage(PracticeCore::getPrefixCore() . "§aType §l§cConfirm §r§a to confirm\n§aพิมพ์ §l§cConfirm §r§a เพื่อยืนยัน");
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
        });
        $form->setTitle('§l§cEdit Kit');
        $form->setContent('§7Select a kit to edit');
        $form->addButton('§aBuild Kit');
        $player->sendForm($form);
    }

    public function botForm($player): void
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            if ($data !== null) {
                switch ($data) {
                    case 0:
                        /* @var $player PracticePlayer */
                        $player->setCurrentKit(KitRegistry::fromString('Fist'));
                        $player->queueBotDuel('Fist');
                        break;
                    case 1:
                        /* @var $player PracticePlayer */
                        $player->setCurrentKit(KitRegistry::fromString('NoDebuff'));
                        $player->queueBotDuel('NoDebuff');
                        break;
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cMenu');
        $form->setContent('§dPlayers: §e' . $this->getQueueBot());
        $form->addButton('§aFist §dBot', 0, 'textures/items/diamond.png');
        $form->addButton('§aNoDebuff §dBot', 1, 'textures/items/diamond.png');
        $player->sendForm($form);
    }

    private function getQueueBot(): int
    {
        $count = 0;
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (($player instanceof PracticePlayer) && $player->isDueling() && str_contains($player->getWorld()->getFolderName(), 'bot')) {
                $count++;
            }
        }
        return $count;
    }

    public function ProfileForm(PracticePlayer $player, ?PracticePlayer $player2): void
    {
        $form = new CustomForm(static function (Player $player, $data) {
        });
        if ($player2 instanceof PracticePlayer) {
            $data = $player2->getData();
            $name = $player2->getName();
        } else {
            $data = $player->getData();
            $name = $player->getName();
        }
        $form->setTitle("$name's §cProfile");
        $form->addLabel(
            '§aKills§f: §e' . $data?->getKills() .
            "\n§e" .
            "\n§aDeath§f: §e" . $data?->getDeaths() .
            "\n§e" .
            "\n§aKDR§f: §e" . $data?->getKdr() .
            "\n§e" .
            "\n§aElo§f: §e" . $data?->getElo()
        );
        $player->sendForm($form);
    }
}