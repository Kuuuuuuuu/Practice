<?php

declare(strict_types=1);

namespace Kohaku\Core;

use Exception;
use JsonException;
use Kohaku\Core\Utils\ScoreboardManager;
use pocketmine\{entity\Skin,
    network\mcpe\protocol\PlayerListPacket,
    network\mcpe\protocol\types\PlayerListEntry,
    player\Player,
    Server
};
use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityDamageEvent};

class HorizonPlayer extends Player
{

    public string $cape = "";
    public string $artifact = "";
    public bool $vanish = false;
    private float|int $xzKB = 0.4;
    private float|int $yKb = 0.4;
    private int $sec = 0;
    private array $validstuffs = [];
    private string $lastDamagePlayer = "Unknown";

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
                    if (Loader::getKnockbackManager()->getAttackspeed($this->getWorld()->getFolderName()) !== null) {
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

    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void
    {
        try {
            if (Loader::getKnockbackManager()->getKnockback($this->getWorld()->getFolderName()) !== null) {
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

    public function updatePlayer()
    {
        $name = $this->getName();
        $this->sec++;
        $this->Vanish();
        if ($this->sec % 3 === 0) {
            $this->updateTag();
            $this->updateScoreboard();
        }
        if ($this->sec % 10 === 0) {
            if (Loader::getInstance()->getArenaUtils()->getData($name)->getTag() !== null and Loader::getInstance()->getArenaUtils()->getData($name)->getTag() !== "") {
                $nametag = Loader::getInstance()->getArenaUtils()->getData($name)->getRank() . "§a " . $this->getDisplayName() . " §f[" . Loader::getInstance()->getArenaUtils()->getData($name)->getTag() . "§f]";
            } else {
                $nametag = Loader::getInstance()->getArenaUtils()->getData($name)->getRank() . "§a " . $this->getDisplayName();
            }
            $this->setNameTag($nametag);
        }
        if (isset(Loader::getInstance()->CombatTimer[$name])) {
            if (Loader::getInstance()->CombatTimer[$name] > 0) {
                $percent = floatval(Loader::getInstance()->CombatTimer[$name] / 10);
                $this->getXpManager()->setXpProgress($percent);
                Loader::getInstance()->CombatTimer[$name] -= 1;
            } else {
                $this->getXpManager()->setXpProgress(0.0);
                $this->sendMessage(Loader::getInstance()->MessageData["StopCombat"]);
                unset(Loader::getInstance()->BoxingPoint[$name ?? null]);
                unset(Loader::getInstance()->CombatTimer[$name]);
                unset(Loader::getInstance()->PlayerOpponent[$name]);
                $this->setUnPVPTag();
            }
        }
    }

    private function Vanish()
    {
        if ($this->vanish) {
            foreach ($this->getEffects() as $effect) {
                if ($effect->isVisible()) {
                    $effect->setVisible(false);
                }
            }
            foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
                $onlinePlayer->hidePlayer($this);
                $entry = new PlayerListEntry();
                $entry->uuid = $this->getUniqueId();
                $pk = new PlayerListPacket();
                $pk->entries[] = $entry;
                $pk->type = PlayerListPacket::TYPE_REMOVE;
                $onlinePlayer->getNetworkSession()->sendDataPacket($pk);
            }
        } else {
            foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
                $onlinePlayer->showPlayer($this);
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
        $tagparkour = "§f[§b {mins} §f: §b{secs} §f: §b{mili} {ping}ms §f]\n §f[§b Jump Count§f: §b{jump} §f]";
        $tagparkour = str_replace("{ping}", (string)$ping, $tagparkour);
        if (isset(Loader::getInstance()->JumpCount[$name])) {
            $tagparkour = str_replace("{jump}", (string)Loader::getInstance()->JumpCount[$name] ?? null, $tagparkour);
        } else {
            $tagparkour = str_replace("{jump}", "0", $tagparkour);
        }
        if (isset(Loader::getInstance()->TimerData[$name])) {
            $tagparkour = str_replace("{mili}", (string)floor(Loader::getInstance()->TimerData[$name] % 100), $tagparkour);
            $tagparkour = str_replace("{secs}", (string)floor((Loader::getInstance()->TimerData[$name] / 100) % 60), $tagparkour);
            $tagparkour = str_replace("{mins}", (string)floor(Loader::getInstance()->TimerData[$name] / 6000), $tagparkour);
        } else {
            $tagparkour = str_replace("{mili}", "0", $tagparkour);
            $tagparkour = str_replace("{secs}", "0", $tagparkour);
            $tagparkour = str_replace("{mins}", "0", $tagparkour);
        }
        $this->setScoreTag($tagparkour);
    }

    public function setPVPTag()
    {
        $ping = $this->getNetworkSession()->getPing();
        $nowcps = Loader::getClickHandler()->getClicks($this);
        $tagpvp = "§b" . $ping . "§fms §f| §b" . $nowcps . " §fCPS";
        $this->setScoreTag($tagpvp);
    }

    public function setUnPVPTag()
    {
        $untagpvp = "§b" . Loader::getInstance()->getArenaUtils()->getPlayerOs($this) . " §f| §b" . Loader::getInstance()->getArenaUtils()->getPlayerControls($this) . " §f| §b" . Loader::getInstance()->getArenaUtils()->getToolboxCheck($this);
        $this->setScoreTag($untagpvp);
    }

    public function updateScoreboard()
    {
        if ($this->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
            Loader::getInstance()->getScoreboardManager()->sb($this);
        } else if ($this->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld() and $this->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getParkourArena())) {
            Loader::getInstance()->getScoreboardManager()->sb2($this);
        } else if ($this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getParkourArena())) {
            Loader::getInstance()->getScoreboardManager()->Parkour($this);
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

    public function boxingTip()
    {
        $name = $this->getName();
        if (isset(Loader::getInstance()->BoxingPoint[$name])) {
            $point = Loader::getInstance()->BoxingPoint[$name];
            $opponent = Loader::getInstance()->BoxingPoint[Loader::getInstance()->PlayerOpponent[$name ?? null] ?? null] ?? 0;
            $this->sendTip("§aYour Points: §f" . $point . " | §cOpponent: §f" . $opponent . " | §bCPS: §f" . Loader::getClickHandler()->getClicks($this));
        } else {
            Loader::getInstance()->BoxingPoint[$name] = 0;
        }
    }
}
