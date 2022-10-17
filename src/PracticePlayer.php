<?php

declare(strict_types=1);

namespace Kuu;

use Exception;
use JsonException;
use Kuu\Utils\Kits\KitManager;
use pocketmine\{entity\Skin, item\VanillaItems, player\GameMode, player\Player, Server};
use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityDamageEvent};
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use Throwable;

class PracticePlayer extends Player
{
    /* Parkour Data */
    public bool $HidePlayer = false;
    public bool $ParkourTimer = false;
    public int $TimerSec = 0;
    public array $ParkourCheckPoint = [
        'x' => 275,
        'y' => 66,
        'z' => 212,
    ];

    /* Client Data */
    public string $Input = 'Unknown';
    public string $Device = 'Unknown';
    public string $OS = 'Unknown';

    /* Combat Data */
    public int $CombatTime = 0;
    public int $BoxingPoint = 0;
    public int $PearlCooldown = 0;
    private int $kills = 0;
    private int $deaths = 0;
    private int $killStreak = 0;
    private ?string $Opponent = null;

    /* Duel Data */
    private bool $isDueling = false;
    private bool $inQueue = false;
    private bool $isCombat = false;
    private ?KitManager $selectKit = null;

    /* Other Data */
    private bool $loadedData = false;
    private string $customTag = '';
    private string $cape = '';
    private string $artifact = '';
    private ?string $EditKit = null;
    private array $savekitcache = [];
    private array $validstuffs = [
        'Adidas',
        'AngelWing',
        'AngelWingV2',
        'Antler',
        'Axe',
        'BackCap',
        'Backpack',
        'BackStabKnife',
        'Bald Headband',
        'Banana',
        'BlackAngleSet',
        'BlazeRod',
        'BlueWing',
        'Boxing',
        'Bubble',
        'Creeper',
        'Crown',
        'CrownV2',
        'DevilHaloWing',
        'DevilWing',
        'Dollar',
        'DragonWing',
        'EnderTail',
        'EnderWing',
        'Fox',
        'Glasses',
        'Goat',
        'Gudoudame',
        'Halo',
        'HeadphoneNote',
        'Headphones',
        'Kaqune',
        'Katana',
        'Koala',
        'LightSaber',
        'LouisVuitton',
        'MiniAngelWing',
        'MiniAngelWingV2',
        'MLG RUSH 1st',
        'Moustache',
        'Neckite',
        'Nike',
        'PhantomWing',
        'Questionmark',
        'Rabbit Costume',
        'Rabbit',
        'RedWing',
        'Rich Bandanna',
        'Santa',
        'Sickle',
        'SP-BananaMan',
        'Susanno',
        'SusanooBlue',
        'SusanooPurple',
        'SWAT Shield',
        'ThunderCloud',
        'UFO',
        'Viking',
        'Wave Bandanna',
        'White Heart',
        'Witchhat',
        'Wither Head'
    ];

    public function attack(EntityDamageEvent $source): void
    {
        $attackSpeed = 10;
        parent::attack($source);
        if ($source->isCancelled()) {
            return;
        }
        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();
            if ($damager instanceof Player) {
                try {
                    if ($this->isDueling()) {
                        if ($this->selectKit?->getName() === 'Sumo') {
                            $attackSpeed = 9;
                        } else {
                            $attackSpeed = 7.5;
                        }
                    } elseif (PracticeCore::getKnockbackManager()->getAttackspeed($this->getWorld()->getFolderName()) !== null) {
                        $attackSpeed = PracticeCore::getKnockbackManager()->getAttackspeed($this->getWorld()->getFolderName()) ?? 10;
                    }
                } catch (Throwable) {
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
        $xzKB = 0.4;
        $yKb = 0.4;
        try {
            if ($this->isDueling()) {
                if ($this->selectKit?->getName() === 'Sumo') {
                    $yKb = 0.388;
                    $xzKB = 0.42;
                } else {
                    $yKb = 0.32;
                    $xzKB = 0.34;
                }
            } elseif (PracticeCore::getKnockbackManager()->getKnockback($this->getWorld()->getFolderName()) !== null) {
                $xzKB = PracticeCore::getKnockbackManager()->getKnockback($this->getWorld()->getFolderName())['hkb'] ?? 0.4;
                $yKb = PracticeCore::getKnockbackManager()->getKnockback($this->getWorld()->getFolderName())['ykb'] ?? 0.4;
            }
        } catch (Throwable) {
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
            $motion->x += $x * $f * $xzKB;
            $motion->y += $yKb;
            $motion->z += $z * $f * $xzKB;
            if ($motion->y > $yKb) {
                $motion->y = $yKb;
            }
            $this->setMotion($motion);
        }
    }

    public function setStuff(string $stuff): void
    {
        $this->artifact = $stuff;
    }

    public function getCape(): string
    {
        return $this->cape;
    }

    public function setCape(string $cape): void
    {
        $this->cape = $cape;
    }

    public function getValidStuffs(): array
    {
        return $this->validstuffs;
    }

    public function getChatFormat(string $message): string
    {
        if ($this->customTag !== '') {
            $nametag = '§f[' . $this->customTag . '§f] §b' . $this->getDisplayName() . '§r§a > §r' . $message;
        } else {
            $nametag = '§a ' . $this->getDisplayName() . '§r§a > §r' . $message;
        }
        return $nametag;
    }

    public function onUpdate(int $currentTick): bool
    {
        if ($currentTick % 3 === 0) {
            $this->updateTag();
            $this->updateScoreboard();
        }
        if ($currentTick % 20 === 0) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                if ($this->HidePlayer) {
                    $this->hidePlayer($player);
                } else {
                    $this->showPlayer($player);
                }
            }
            if ($this->isCombat()) {
                $this->CombatTime--;
                if ($this->CombatTime <= 0) {
                    $this->setCombat(false);
                    $this->getXpManager()->setXpProgress(0.0);
                    $this->sendMessage(PracticeCore::getInstance()->MessageData['StopCombat']);
                    $this->BoxingPoint = 0;
                    $this->setOpponent(null);
                    $this->setUnPVPTag();
                }
            }
        }
        if ($currentTick % 40 === 0) {
            $this->updateNametag();
            $this->setInputData();
        }
        if ($this->ParkourTimer) {
            $this->TimerSec += 5;
        } else {
            $this->TimerSec = 0;
        }
        return parent::onUpdate($currentTick);
    }

    private function updateTag(): void
    {
        if ($this->isConnected()) {
            if ($this->isCombat() || $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getOITCArena()) || $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getKnockbackArena()) || $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBuildArena())) {
                $this->setPVPTag();
            } elseif (!$this->isCombat() && $this->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getParkourArena())) {
                $this->setUnPVPTag();
            } elseif ($this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getParkourArena())) {
                $this->setParkourTag();
            }
        }
    }

    public function isCombat(): bool
    {
        return $this->isCombat;
    }

    private function setPVPTag(): void
    {
        $ping = $this->getNetworkSession()->getPing();
        $nowcps = PracticeCore::getClickHandler()->getClicks($this);
        $this->sendData($this->getWorld()->getPlayers(), [EntityMetadataProperties::SCORE_TAG => new StringMetadataProperty(PracticeConfig::COLOR . $ping . ' §fMS §f| ' . PracticeConfig::COLOR . $nowcps . ' §fCPS')]);
    }

    private function setUnPVPTag(): void
    {
        $this->sendData($this->getWorld()->getPlayers(), [EntityMetadataProperties::SCORE_TAG => new StringMetadataProperty(PracticeConfig::COLOR . $this->OS . '§f | §f' . $this->Input)]);
    }

    private function setParkourTag(): void
    {
        $ping = $this->getNetworkSession()->getPing();
        $mins = floor($this->TimerSec / 6000);
        $secs = floor(($this->TimerSec / 100) % 60);
        $mili = $this->TimerSec % 100;
        $this->sendData($this->getWorld()->getPlayers(), [EntityMetadataProperties::SCORE_TAG => new StringMetadataProperty(PracticeConfig::COLOR . $ping . ' §fMS §f| §a' . $mins . ' : ' . $secs . ' : ' . $mili)]);
    }

    private function updateScoreboard(): void
    {
        if ($this->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
            PracticeCore::getInstance()->getScoreboardManager()->sb($this);
        } elseif ($this->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld() && $this->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getParkourArena()) && $this->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBoxingArena())) {
            PracticeCore::getInstance()->getScoreboardManager()->sb2($this);
        } elseif ($this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBoxingArena())) {
            PracticeCore::getInstance()->getScoreboardManager()->Boxing($this);
        } elseif ($this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getParkourArena())) {
            PracticeCore::getInstance()->getScoreboardManager()->Parkour($this);
        }
    }

    public function setCombat(bool $bool): void
    {
        if (!$bool && $this->CombatTime > 0) {
            $this->CombatTime = 1;
        } else {
            $this->isCombat = $bool;
            $this->CombatTime = 10;
        }
    }

    private function updateNametag(): void
    {
        if ($this->customTag !== '') {
            $nametag = '§f[' . $this->customTag . '§f] §b' . $this->getDisplayName();
        } else {
            $nametag = '§b' . $this->getDisplayName();
        }
        $this->setNameTag($nametag);
    }

    private function setInputData(): void
    {
        $data = $this->getPlayerInfo()->getExtraData();
        if ($data['CurrentInputMode'] !== null) {
            $this->Input = PracticeConfig::ControlList[$data['CurrentInputMode']];
        }
        if ($data['DeviceOS'] !== null) {
            $this->OS = PracticeConfig::OSList[$data['DeviceOS']];
        }
        if ($data['DeviceModel'] !== null) {
            $this->Device = $data['DeviceModel'];
        }
    }

    public function setDueling(bool $playing): void
    {
        $this->isDueling = $playing;
    }

    public function onJoin(): void
    {
        $this->getEffects()->clear();
        $this->getInventory()->clearAll();
        $this->getArmorInventory()->clearAll();
        $this->setLobbyItem();
        $this->setInputData();
        PracticeCore::getPlayerHandler()->loadPlayerData($this);
        $this->sendMessage(PracticeCore::getPrefixCore() . '§eLoading Data...');
    }

    public function setLobbyItem(): void
    {
        $item = VanillaItems::DIAMOND_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item->setCustomName('§r§bPlay');
        $item2 = VanillaItems::CLOCK()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item2->setCustomName('§r§bSettings');
        $item3 = VanillaItems::GOLDEN_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item3->setCustomName('§r§bBot');
        $item4 = VanillaItems::IRON_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item4->setCustomName('§r§bDuel');
        $item6 = VanillaItems::COMPASS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item6->setCustomName('§r§bProfile');
        $this->getOffHandInventory()->clearAll();
        $this->getInventory()->clearAll();
        $this->getArmorInventory()->clearAll();
        $this->getEffects()->clear();
        $this->getInventory()->setItem(0, $item);
        $this->getInventory()->setItem(8, $item2);
        $this->getInventory()->setItem(1, $item4);
        $this->getInventory()->setItem(2, $item3);
        $this->getInventory()->setItem(4, $item6);
    }

    /**
     * @throws JsonException
     */
    public function loadData(array $data = []): void
    {
        if (isset($data['kills'])) {
            $this->kills = (int)$data['kills'];
        }
        if (isset($data['deaths'])) {
            $this->deaths = (int)$data['deaths'];
        }
        if (isset($data['tag'])) {
            $this->customTag = (string)$data['tag'];
        }
        if (isset($data['cape'])) {
            $this->cape = (string)$data['cape'];
        }
        if (isset($data['artifact'])) {
            $this->artifact = (string)$data['artifact'];
        }
        if (isset($data['killStreak'])) {
            $this->killStreak = (int)$data['killStreak'];
        }
        $this->loadedData = true;
        $this->setCosmetic();
    }

    /**
     * @throws JsonException
     */
    public function setCosmetic(): void
    {
        if (file_exists(PracticeCore::getInstance()->getDataFolder() . 'cosmetic/artifact/' . $this->artifact . '.png')) {
            if ($this->getStuff() !== '' && $this->getStuff() !== null) {
                PracticeCore::getCosmeticHandler()->setSkin($this, $this->artifact);
            }
        } else {
            $this->artifact = '';
        }
        if (file_exists(PracticeCore::getInstance()->getDataFolder() . 'cosmetic/capes/' . $this->cape . '.png')) {
            $oldSkin = $this->getSkin();
            $capeData = PracticeCore::getCosmeticHandler()->createCape($this->cape);
            $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
            $this->setSkin($setCape);
            $this->sendSkin();
        } else {
            $this->cape = '';
        }
    }

    public function getStuff(): string
    {
        return $this->artifact;
    }

    public function getCustomTag(): string
    {
        return $this->customTag;
    }

    public function setCustomTag(string $tag): void
    {
        $this->customTag = $tag;
    }

    public function getStreak(): int
    {
        return $this->killStreak;
    }

    /**
     * @throws Exception
     */
    public function checkQueue(): void
    {
        $this->sendMessage(PracticeCore::getPrefixCore() . 'Entering queue...');
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            if (($player instanceof self && $player->getName() !== $this->getName()) && ($this->isInQueue() && $player->isInQueue()) && $this->getDuelKit() === $player->getDuelKit()) {
                PracticeCore::getInstance()->getDuelManager()->createMatch($this, $player, $this->getDuelKit());
                $this->sendMessage(PracticeCore::getPrefixCore() . 'Found a match against §c' . $player->getName());
                $player->sendMessage(PracticeCore::getPrefixCore() . 'Found a match against §c' . $this->getName());
                foreach ([$player, $this] as $p) {
                    $p->setInQueue(false);
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
        return $this->selectKit ?? null;
    }

    /**
     * @throws Exception
     */
    public function queueBotDuel(int $mode): void
    {
        PracticeCore::getInstance()->getDuelManager()->createBotMatch($this, $this->getDuelKit(), $mode);
        $this->setInQueue(false);
    }

    public function onQuit(): void
    {
        PracticeCore::getClickHandler()->removePlayerClickData($this);
        if ($this->isDueling() || $this->isCombat()) {
            $this->kill();
        }
        PracticeCore::getPlayerHandler()->savePlayerData($this);
        $this->setGamemode(GameMode::SURVIVAL());
    }

    public function setCurrentKit(?KitManager $kit): void
    {
        $this->selectKit = $kit;
    }

    /**
     * @throws JsonException
     */
    public function saveKit(): void
    {
        $name = $this->getName();
        try {
            foreach ($this->getInventory()->getContents() as $slot => $item) {
                $this->savekitcache[$slot] = $item->jsonSerialize();
            }
            PracticeCore::getInstance()->KitData->set($name, $this->savekitcache);
        } catch (Exception) {
            $this->kill();
            $this->setImmobile(false);
            $this->sendMessage(PracticeCore::getPrefixCore() . '§cAn error occurred while saving your kit.');
            $this->EditKit = null;
            return;
        }
        PracticeCore::getInstance()->KitData->save();
        $this->EditKit = null;
        $this->sendMessage(PracticeCore::getPrefixCore() . '§aYou have successfully saved your kit!');
        $this->kill();
        $this->setImmobile(false);
    }

    public function getOpponent(): ?string
    {
        return $this->Opponent;
    }

    public function setOpponent(?string $name): void
    {
        $this->Opponent = $name;
    }

    public function getEditKit(): ?string
    {
        return $this->EditKit;
    }

    public function setEditKit(?string $kit): void
    {
        $this->EditKit = $kit;
    }

    public function addDeath(): void
    {
        if (!isset(PracticeCore::getCaches()->DeathLeaderboard[$this->getName()])) {
            PracticeCore::getCaches()->DeathLeaderboard[$this->getName()] = 1;
        } else {
            PracticeCore::getCaches()->DeathLeaderboard[$this->getName()]++;
        }
        $this->deaths++;
    }

    public function getKills(): int
    {
        return $this->kills;
    }

    public function getDeaths(): int
    {
        return $this->deaths;
    }

    public function getKdr(): float|int
    {
        if ($this->deaths > 0) {
            return $this->kills / $this->deaths;
        }
        return 1;
    }

    public function addKill(): void
    {
        if (!isset(PracticeCore::getCaches()->KillLeaderboard[$this->getName()])) {
            PracticeCore::getCaches()->KillLeaderboard[$this->getName()] = 1;
        } else {
            PracticeCore::getCaches()->KillLeaderboard[$this->getName()]++;
        }
        $this->kills++;
    }

    public function hasLoadedData(): bool
    {
        return $this->loadedData;
    }
}
