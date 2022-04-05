<?php

declare(strict_types=1);

namespace Kohaku;

use Exception;
use JsonException;
use Kohaku\Utils\Kits\KitManager;
use pocketmine\{entity\Skin,
    player\GameMode,
    player\Player,
    Server};
use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityDamageEvent};

class NeptunePlayer extends Player
{

    public string $cape = "";
    public string $artifact = "";
    public ?KitManager $duelKit;
    private float $xzKB = 0.4;
    private float $yKb = 0.4;
    private int $sec = 0;
    private array $validstuffs = [];
    private string $lastDamagePlayer = "Unknown";
    private bool $isDueling = false;
    private bool $inQueue = false;

    public function attack(EntityDamageEvent $source): void
    {
        parent::attack($source);
        if ($source->isCancelled()) {
            return;
        }
        $attackSpeed = $source->getAttackCooldown();
        if ($attackSpeed < 0) $attackSpeed = 0;
        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();
            if ($damager instanceof Player) {
                try {
                    if ($this->isDueling()) {
                        $attackSpeed = 8;
                    } else if (Loader::getKnockbackManager()->getAttackspeed($this->getWorld()->getFolderName()) !== null) {
                        $attackSpeed = Loader::getKnockbackManager()->getAttackspeed($this->getWorld()->getFolderName());
                    } else {
                        $attackSpeed = 10;
                    }
                } catch (Exception) {
                    $attackSpeed = 10;
                }
            }
        }
        $this->attackTime = $attackSpeed;
    }

    public function isDueling(): bool
    {
        return $this->isDueling;
    }

    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void
    {
        try {
            if ($this->isDueling()) {
                $this->yKb = 0.301;
                $this->xzKB = 0.311;
            } else if (Loader::getKnockbackManager()->getKnockback($this->getWorld()->getFolderName()) !== null) {
                $this->xzKB = Loader::getKnockbackManager()->getKnockback($this->getWorld()->getFolderName())["hkb"];
                $this->yKb = Loader::getKnockbackManager()->getKnockback($this->getWorld()->getFolderName())["ykb"];
            } else {
                $this->xzKB = 0.4;
                $this->yKb = 0.4;
            }
        } catch (Exception) {
            $this->xzKB = 0.4;
            $this->yKb = 0.4;
        }
        $f = sqrt($x * $x + $z * $z);
        if ($f <= 0) {
            return;
        }
        if (mt_rand() / mt_getrandmax() > $this->knockbackResistanceAttr->getValue()) {
            $f = 1 / $f;
            $motion = clone $this->motion;
            $motion->x /= 2;
            $motion->y /= 2;
            $motion->z /= 2;
            $motion->x += $x * $f * $this->xzKB;
            $motion->y += $this->yKb;
            $motion->z += $z * $f * $this->xzKB;
            if ($motion->y > $this->yKb) {
                $motion->y = $this->yKb;
            }
            $this->setMotion($motion);
        }
    }

    /**
     * @throws JsonException
     */
    public function setStuff(string $stuff): void
    {
        Loader::getInstance()->ArtifactData->set($this->getName(), $stuff);
        Loader::getInstance()->ArtifactData->save();
    }

    public function getCape(): string
    {
        return $this->cape;
    }

    public function getValidStuffs(): array
    {
        return $this->validstuffs;
    }

    public function setValidStuffs(string $stuff): void
    {
        $key = in_array($stuff, $this->validstuffs);
        if ($key === false) {
            $this->validstuffs[] = $stuff;
        }
    }

    public function getKit(): array
    {
        return array(Loader::getInstance()->KitData->get($this->getName()) ? Loader::getInstance()->KitData->get($this->getName()) : "");
    }

    public function getAllArtifact()
    {
        $this->setValidStuffs("Adidas");
        $this->setValidStuffs("AngelWing");
        $this->setValidStuffs("AngelWingV2");
        $this->setValidStuffs("Antler");
        $this->setValidStuffs("Axe");
        $this->setValidStuffs("BackCap");
        $this->setValidStuffs("Backpack");
        $this->setValidStuffs("BackStabKnife");
        $this->setValidStuffs("Bald Headband");
        $this->setValidStuffs("Banana");
        $this->setValidStuffs("BlackAngleSet");
        $this->setValidStuffs("BlazeRod");
        $this->setValidStuffs("BlueWing");
        $this->setValidStuffs("Boxing");
        $this->setValidStuffs("Bubble");
        $this->setValidStuffs("Creeper");
        $this->setValidStuffs("Crown");
        $this->setValidStuffs("CrownV2");
        $this->setValidStuffs("DevilHaloWing");
        $this->setValidStuffs("DevilWing");
        $this->setValidStuffs("Dollar");
        $this->setValidStuffs("DragonWing");
        $this->setValidStuffs("EnderTail");
        $this->setValidStuffs("EnderWing");
        $this->setValidStuffs("Fox");
        $this->setValidStuffs("Glasses");
        $this->setValidStuffs("Goat");
        $this->setValidStuffs("Gudoudame");
        $this->setValidStuffs("Halo");
        $this->setValidStuffs("HeadphoneNote");
        $this->setValidStuffs("Headphones");
        $this->setValidStuffs("Kaqune");
        $this->setValidStuffs("Katana");
        $this->setValidStuffs("Koala");
        $this->setValidStuffs("LightSaber");
        $this->setValidStuffs("LouisVuitton");
        $this->setValidStuffs("MiniAngelWing");
        $this->setValidStuffs("MiniAngelWingV2");
        $this->setValidStuffs("MLG RUSH 1st");
        $this->setValidStuffs("Moustache");
        $this->setValidStuffs("Neckite");
        $this->setValidStuffs("Nike");
        $this->setValidStuffs("PhantomWing");
        $this->setValidStuffs("Questionmark");
        $this->setValidStuffs("Rabbit Costume");
        $this->setValidStuffs("Rabbit");
        $this->setValidStuffs("RedWing");
        $this->setValidStuffs("Rich Bandanna");
        $this->setValidStuffs("Santa");
        $this->setValidStuffs("Sickle");
        $this->setValidStuffs("SP-BananaMan");
        $this->setValidStuffs("Susanno");
        $this->setValidStuffs("SusanooBlue");
        $this->setValidStuffs("SusanooPurple");
        $this->setValidStuffs("SWAT Shield");
        $this->setValidStuffs("ThunderCloud");
        $this->setValidStuffs("UFO");
        $this->setValidStuffs("Viking");
        $this->setValidStuffs("Wave Bandanna");
        $this->setValidStuffs("White Heart");
        $this->setValidStuffs("Witchhat");
        $this->setValidStuffs("Wither Head");
    }

    public function getLastDamagePlayer(): string
    {
        return $this->lastDamagePlayer;
    }

    public function setLastDamagePlayer(string $name): void
    {
        $this->lastDamagePlayer = $name;
    }

    public function updateCPS()
    {
        switch ($this->getWorld()) {
            case Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getParkourArena()):
                $this->parkourTimer();
                break;
            default:
                $this->sendTip("§dCPS: §f" . Loader::getClickHandler()->getClicks($this));
                break;
        }
    }

    public function parkourTimer()
    {
        $name = $this->getName();
        if (isset(Loader::getInstance()->TimerTask[$name])) {
            if (Loader::getInstance()->TimerTask[$name] === true) {
                if (isset(Loader::getInstance()->TimerData[$name])) {
                    Loader::getInstance()->TimerData[$name] += 5;
                } else {
                    Loader::getInstance()->TimerData[$name] = 0;
                }
                $mins = floor(Loader::getInstance()->TimerData[$name] / 6000);
                $secs = floor((Loader::getInstance()->TimerData[$name] / 100) % 60);
                $mili = Loader::getInstance()->TimerData[$name] % 100;
                $this->sendTip("§a" . $mins . " : " . $secs . " : " . $mili);
            } else {
                $this->sendTip("§a0 : 0 : 0");
                Loader::getInstance()->TimerData[$name] = 0;
            }
        }
    }

    public function updatePlayer()
    {
        $name = $this->getName();
        $this->sec++;
        $this->updateTag();
        if ($this->sec % 2 === 0) {
            $this->updateScoreboard();
            $this->updateNametag();
        }
        if (isset(Loader::getInstance()->CombatTimer[$name])) {
            if (Loader::getInstance()->CombatTimer[$name] > 0) {
                $percent = floatval(Loader::getInstance()->CombatTimer[$name] / 10);
                $this->getXpManager()->setXpProgress($percent);
                Loader::getInstance()->CombatTimer[$name] -= 1;
            } else {
                $this->getXpManager()->setXpProgress(0.0);
                $this->sendMessage(Loader::getInstance()->MessageData["StopCombat"]);
                unset(Loader::getInstance()->BoxingPoint[$name]);
                unset(Loader::getInstance()->CombatTimer[$name]);
                unset(Loader::getInstance()->PlayerOpponent[$name]);
                $this->setUnPVPTag();
            }
        }
    }

    public function updateTag()
    {
        $name = $this->getName();
        if ($this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getParkourArena())) {
            $this->setParkourTag();
        } else {
            if (isset(Loader::getInstance()->CombatTimer[$name]) or $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getSumoDArena()) or $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getKitPVPArena()) or $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getOITCArena()) or $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getKnockbackArena()) or $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBuildArena())) {
                $this->setPVPTag();
            } else if (!isset(Loader::getInstance()->CombatTimer[$name])) {
                $this->setUnPVPTag();
            }
        }
    }

    public function setParkourTag()
    {
        $name = $this->getName();
        $ping = $this->getNetworkSession()->getPing();
        $tagparkour = "§f[§d {mins} §f: §d{secs} §f: §d{mili} {ping}ms §f]";
        $tagparkour = str_replace("{ping}", (string)$ping, $tagparkour);
        $tagparkour = str_replace("{mili}", (string)floor(Loader::getInstance()->TimerData[$name] ?? 0 % 100), $tagparkour);
        $tagparkour = str_replace("{secs}", (string)floor((Loader::getInstance()->TimerData[$name] ?? 0 / 100) % 60), $tagparkour);
        $tagparkour = str_replace("{mins}", (string)floor(Loader::getInstance()->TimerData[$name] ?? 0 / 6000), $tagparkour);
        $this->setScoreTag($tagparkour);
    }

    public function setPVPTag()
    {
        $ping = $this->getNetworkSession()->getPing();
        $nowcps = Loader::getClickHandler()->getClicks($this);
        $tagpvp = "§d" . $ping . "§fms §f| §d" . $nowcps . " §fCPS";
        $this->setScoreTag($tagpvp);
    }

    public function setUnPVPTag()
    {
        $untagpvp = "§d" . Loader::getInstance()->getArenaUtils()->getPlayerOs($this) . " §f| §d" . Loader::getInstance()->getArenaUtils()->getPlayerControls($this) . " §f| §d" . Loader::getInstance()->getArenaUtils()->getToolboxCheck($this);
        $this->setScoreTag($untagpvp);
    }

    public function updateScoreboard()
    {
        if ($this->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
            Loader::getInstance()->getScoreboardManager()->sb($this);
        } else if ($this->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld() and $this->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBoxingArena())) {
            Loader::getInstance()->getScoreboardManager()->sb2($this);
        } else if ($this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBoxingArena())) {
            Loader::getInstance()->getScoreboardManager()->Boxing($this);
        }
    }

    public function updateNametag()
    {
        $name = $this->getName();
        if (Loader::getInstance()->getArenaUtils()->getData($name)->getTag() !== null and Loader::getInstance()->getArenaUtils()->getData($name)->getTag() !== "") {
            $nametag = Loader::getInstance()->getArenaUtils()->getData($name)->getRank() . "§a " . $this->getDisplayName() . " §f[" . Loader::getInstance()->getArenaUtils()->getData($name)->getTag() . "§f]";
        } else {
            $nametag = Loader::getInstance()->getArenaUtils()->getData($name)->getRank() . "§a " . $this->getDisplayName();
        }
        $this->setNameTag($nametag);
    }

    public function setDueling(bool $playing): void
    {
        $this->isDueling = $playing;
    }

    /**
     * @throws JsonException
     */
    public function onJoin()
    {
        $name = $this->getName();
        $this->getEffects()->clear();
        $this->getInventory()->clearAll();
        $this->getArmorInventory()->clearAll();
        Loader::getInstance()->getArenaUtils()->GiveItem($this);
        $this->LoadData();
        $this->sendMessage(Loader::getPrefixCore() . "§eLoading Player Data");
        if (isset(Loader::getInstance()->EditKit[$name])) {
            unset(Loader::getInstance()->EditKit[$name]);
        }
    }

    /**
     * @throws JsonException
     */
    public function LoadData()
    {
        $this->cape = Loader::getInstance()->CapeData->get($this->getName()) ? Loader::getInstance()->CapeData->get($this->getName()) : "";
        $this->artifact = Loader::getInstance()->ArtifactData->get($this->getName()) ? Loader::getInstance()->ArtifactData->get($this->getName()) : "";
        $this->setCosmetic();
    }

    /**
     * @throws JsonException
     */
    public function setCosmetic(): void
    {
        if (file_exists(Loader::getInstance()->getDataFolder() . "cosmetic/artifact/" . Loader::getInstance()->ArtifactData->get($this->getName()) . ".png")) {
            if ($this->getStuff() !== "" or $this->getStuff() !== null) {
                Loader::getCosmeticHandler()->setSkin($this, $this->getStuff());
            }
        } else {
            Loader::getInstance()->ArtifactData->remove($this->getName());
            Loader::getInstance()->ArtifactData->save();
        }
        if (file_exists(Loader::getInstance()->getDataFolder() . "cosmetic/capes/" . Loader::getInstance()->CapeData->get($this->getName()) . ".png")) {
            $oldSkin = $this->getSkin();
            $capeData = Loader::getCosmeticHandler()->createCape(Loader::getInstance()->CapeData->get($this->getName()));
            $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
            $this->setSkin($setCape);
            $this->sendSkin();
        } else {
            Loader::getInstance()->CapeData->remove($this->getName());
            Loader::getInstance()->CapeData->save();
        }
    }

    public function getStuff(): string
    {
        return $this->artifact;
    }

    public function checkQueue(): void
    {
        $this->sendMessage(Loader::getPrefixCore() . "Entering queue...");
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            if ($player instanceof NeptunePlayer and $player->getName() !== $this->getName()) {
                if ($player->isInQueue() and $this->getDuelKit() === $player->getDuelKit()) {
                    Loader::getInstance()->getDuelManager()->createMatch($this, $player, $this->getDuelKit());
                    $this->sendMessage(Loader::getPrefixCore() . "Found a match against §c" . $player->getName());
                    $player->sendMessage(Loader::getPrefixCore() . "Found a match against §c" . $this->getName());
                    $player->setInQueue(false);
                    $this->setInQueue(false);
                    return;
                }
            }
        }
    }

    public function isInQueue(): bool
    {
        return $this->inQueue;
    }

    public function setInQueue(bool $inQueue): void
    {
        $this->inQueue = $inQueue;
    }

    public function getDuelKit(): ?KitManager
    {
        return $this->duelKit ?? null;
    }

    public function onQuit()
    {
        $name = $this->getName();
        $this->getEffects()->clear();
        $this->getInventory()->clearAll();
        $this->getArmorInventory()->clearAll();
        Loader::getClickHandler()->removePlayerClickData($this);
        if (isset(Loader::getInstance()->BoxingPoint[$name])) {
            unset(Loader::getInstance()->BoxingPoint[$name]);
        }
        if (isset(Loader::getInstance()->PlayerOpponent[$name])) {
            Loader::getInstance()->BoxingPoint[Loader::getInstance()->PlayerOpponent[$name]] = 0;
            unset(Loader::getInstance()->PlayerOpponent[$name]);
        }
        if (isset(Loader::getInstance()->CombatTimer[$name])) {
            $this->kill();
            unset(Loader::getInstance()->CombatTimer[$name]);
        }
        if (isset(Loader::getInstance()->EditKit[$name])) {
            unset(Loader::getInstance()->EditKit[$name]);
        }
        if ($this->isDueling()) {
            $this->kill();
        }
        $this->setInQueue(false);
        $this->setCurrentKit(null);
        $this->setGamemode(GameMode::SURVIVAL());
    }

    public function setCurrentKit(?KitManager $kit): void
    {
        $this->duelKit = $kit;
    }

    /**
     * @throws JsonException
     */
    public function saveKit()
    {
        $name = $this->getName();
        try {
            Loader::getInstance()->KitData->set($name, [
                "0" => [
                    "item" => $this->getInventory()->getItem(0)->getId(),
                    "count" => $this->getInventory()->getItem(0)->getCount(),
                ],
                "1" => [
                    "item" => $this->getInventory()->getItem(1)->getId(),
                    "count" => $this->getInventory()->getItem(1)->getCount()
                ],
                "2" => [
                    "item" => $this->getInventory()->getItem(2)->getId(),
                    "count" => $this->getInventory()->getItem(2)->getCount(),
                ],
                "3" => [
                    "item" => $this->getInventory()->getItem(3)->getId(),
                    "count" => $this->getInventory()->getItem(3)->getCount()
                ],
                "4" => [
                    "item" => $this->getInventory()->getItem(4)->getId(),
                    "count" => $this->getInventory()->getItem(4)->getCount()
                ],
                "5" => [
                    "item" => $this->getInventory()->getItem(5)->getId(),
                    "count" => $this->getInventory()->getItem(5)->getCount()
                ],
                "6" => [
                    "item" => $this->getInventory()->getItem(6)->getId(),
                    "count" => $this->getInventory()->getItem(6)->getCount()
                ],
                "7" => [
                    "item" => $this->getInventory()->getItem(7)->getId(),
                    "count" => $this->getInventory()->getItem(7)->getCount()
                ],
                "8" => [
                    "item" => $this->getInventory()->getItem(8)->getId(),
                    "count" => $this->getInventory()->getItem(8)->getCount()
                ]
            ]);
        } catch (Exception) {
            $this->kill();
            $this->setImmobile(false);
            $this->sendMessage(Loader::getPrefixCore() . "§cAn error occurred while saving your kit.");
            unset(Loader::getInstance()->EditKit[$name]);
            return;
        }
        Loader::getInstance()->KitData->save();
        unset(Loader::getInstance()->EditKit[$name]);
        $this->sendMessage(Loader::getPrefixCore() . "§aYou have successfully saved your kit!");
        $this->kill();
        $this->setImmobile(false);
    }
}
