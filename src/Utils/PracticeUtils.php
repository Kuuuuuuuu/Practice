<?php

declare(strict_types=1);

namespace Kuu\Utils;

use DateTime;
use Exception;
use Kuu\Commands\BroadcastCommand;
use Kuu\Commands\HubCommand;
use Kuu\Commands\PlayerInfoCommand;
use Kuu\Commands\PracticeCommand;
use Kuu\Commands\RestartCommand;
use Kuu\Commands\SetTagCommand;
use Kuu\Commands\TbanCommand;
use Kuu\Commands\TcheckCommand;
use Kuu\Commands\TpsCommand;
use Kuu\Entity\ArrowEntity;
use Kuu\Entity\BaseLeaderboard;
use Kuu\Entity\DeathLeaderboard;
use Kuu\Entity\EnderPearlEntity;
use Kuu\Entity\FallingWool;
use Kuu\Entity\KillLeaderboard;
use Kuu\Entity\PracticeBot;
use Kuu\Events\PracticeListener;
use Kuu\Items\Bow;
use Kuu\Items\CustomSplashPotion;
use Kuu\Items\EnderPearl;
use Kuu\PracticeConfig;
use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use Kuu\Task\PracticeTask;
use Kuu\Utils\Discord\DiscordWebhook;
use Kuu\Utils\Discord\DiscordWebhookEmbed;
use Kuu\Utils\Discord\DiscordWebhookUtils;
use Kuu\Utils\Generator\SumoGenerator;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\raklib\RakLibInterface;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use SQLite3;
use Throwable;
use ZipArchive;

class PracticeUtils
{

    public static function playSound(string $soundName, Player $player): void
    {
        $location = $player->getLocation();
        $pk = new PlaySoundPacket();
        $pk->soundName = $soundName;
        $pk->volume = 1;
        $pk->pitch = 1;
        $pk->x = $location->x;
        $pk->y = $location->y;
        $pk->z = $location->z;
        $player->getNetworkSession()->sendDataPacket($pk, true);
    }

    public function ChunkLoader(Player $player): void
    {
        $pos = $player->getPosition();
        PracticeCore::getInstance()->getPracticeUtils()->onChunkGenerated($pos->world, (int)$player->getPosition()->getX() >> 4, (int)$player->getPosition()->getZ() >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
    }

    public static function onChunkGenerated(World $world, int $x, int $z, callable $callable): void
    {
        if ($world->isChunkPopulated($x, $z)) {
            ($callable)();
        } else {
            $world->registerChunkLoader(new ChunkManager($world, $x, $z, $callable), $x, $z);
        }
    }

    /**
     * @throws Exception
     */

    public function generateUUID()
    {
        $data = random_bytes(16);
        assert(strlen($data) === 16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function DeviceCheck(Player $player): void
    {
        $username = $player->getName();
        $data = $player->getPlayerInfo()->getExtraData();
        if ($player instanceof PracticePlayer) {
            if ($data['CurrentInputMode'] !== null) {
                $player->PlayerControl = PracticeConfig::ControlList[$data['CurrentInputMode']];
            }
            if ($data['DeviceOS'] !== null) {
                $player->PlayerOS = PracticeConfig::OSList[$data['DeviceOS']];
            }
            if ($data['DeviceModel'] !== null) {
                $player->PlayerDevice = $data['DeviceModel'];
            }
            $deviceOS = (int)$data['DeviceOS'];
            $deviceModel = (string)$data['DeviceModel'];
            if ($deviceOS !== 1) {
                return;
            }
            $name = explode(' ', $deviceModel);
            if (!isset($name[0])) {
                return;
            }
            $check = strtoupper($name[0]);
            if ($check !== $name[0]) {
                Server::getInstance()->broadcastMessage(PracticeCore::getPrefixCore() . '§e' . $username . ' §cMight be Using §aToolbox. Please Avoid that Player!');
                $player->ToolboxStatus = 'Toolbox';
            }
        }
    }

    public function Enable(): void
    {
        $this->registerItems();
        $this->registerConfigs();
        $this->registerCommands();
        $this->registerEvents();
        $this->registerTasks();
        $this->registerEntitys();
        $this->registerGenerators();
        foreach (glob(Server::getInstance()->getDataPath() . 'worlds/*') as $world) {
            $world = str_replace(Server::getInstance()->getDataPath() . 'worlds/', '', $world);
            if (Server::getInstance()->getWorldManager()->isWorldLoaded($world)) {
                continue;
            }
            Server::getInstance()->getWorldManager()->loadWorld($world, true);
        }
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            $world->setTime(0);
            $world->stopTime();
        }
        foreach (Server::getInstance()->getNetwork()->getInterfaces() as $interface) {
            if ($interface instanceof RakLibInterface) {
                $interface->setPacketLimit(9999999999);
            }
        }
        Server::getInstance()->getNetwork()->setName(PracticeConfig::MOTD);
    }

    private function registerItems(): void
    {
        foreach (PotionType::getAll() as $type) {
            $typeId = PotionTypeIdMap::getInstance()->toId($type);
            ItemFactory::getInstance()->register(new CustomSplashPotion(new ItemIdentifier(ItemIds::SPLASH_POTION, $typeId), $type->getDisplayName() . ' Splash Potion', $type), true);
        }
        ItemFactory::getInstance()->register(new EnderPearl(new ItemIdentifier(ItemIds::ENDER_PEARL, 0), 'Ender Pearl'), true);
        ItemFactory::getInstance()->register(new Bow(new ItemIdentifier(ItemIds::BOW, 0), 'Bow'), true);
    }

    private function registerConfigs(): void
    {
        @mkdir(PracticeCore::getInstance()->getDataFolder() . 'data/');
        @mkdir(PracticeCore::getInstance()->getDataFolder() . 'players/');
        @mkdir(PracticeCore::getInstance()->getDataFolder() . 'Kits/');
        PracticeCore::getInstance()->saveResource('config.yml');
        PracticeCore::getInstance()->KitData = new Config(PracticeCore::getInstance()->getDataFolder() . 'KitData.json', Config::JSON);
        PracticeCore::getInstance()->ArtifactData = new Config(PracticeCore::getInstance()->getDataFolder() . 'ArtifactData.yml', Config::YAML);
        PracticeCore::getInstance()->CapeData = new Config(PracticeCore::getInstance()->getDataFolder() . 'CapeData.yml', Config::YAML);
        PracticeCore::getInstance()->MessageData = (new Config(PracticeCore::getInstance()->getDataFolder() . 'messages.yml', Config::YAML, array(
            'StartCombat' => '§dNeptune§f » §r§aYou Started combat!',
            'AntiCheatName' => '§fLunar §f» ',
            'CooldownMessage' => "§dNeptune§f » §r§cYou can't chat for {cooldown} seconds!",
            'StopCombat' => '§dNeptune§f » §r§aYou Cleared combat!',
            'StartSkillMessage' => '§dNeptune§f » §r§aYou Started Skill!',
            'NoPlayer' => '§dNeptune§f » §r§cPlayer not found!',
            'SkillCleared' => '§dNeptune§f » §r§aSkill Cleared!',
            'CantUseeditkit' => "§dNeptune§f » §r§cYou can't use this command in editkit mode!",
            'CantUseWantCombat' => "§dNeptune§f » §r§cYou can't use this command in combat!",
            'BroadcastBanMessage' => "§f––––––––––––––––––––––––\n§ePlayer §f: §c{player}\n§eHas banned: §c{day}§eD §f| §c{hour}§eH §f| §c{minute}§eM\n§eReason: §c{reason}\n§f––––––––––––––––––––––––§f",
            'KickBanMessage' => "§fLunar\n§cYou Are Banned\n§6Reason : §f{reason}\n§6Unban At §f: §e{day} D §f| §e{hour} H §f| §e{minute} M",
            'LoginBanMessage' => "§fLunar\n§cYou Are Banned\n§6Reason : §f{reason}\n§6Unban At §f: §e{day} D §f| §e{hour} H §f| §e{minute} M",
            'BanMyself' => "§dNeptune§f » §cYou can't ban yourself",
            'NoBanPlayers' => '§dNeptune§f » §aNo ban players',
            'UnBanPlayer' => '§dNeptune§f » §b{player} §ahas been unban',
            'AutoUnBanPlayer' => '§dNeptune§f »» §a{player} Has Auto Unban Already!',
            'BanListTitle' => PracticeConfig::Server_Name . '§eBanSystem',
            'BanListContent' => '§c§lChoose player',
            'PlayerListTitle' => PracticeConfig::Server_Name . '§eBanSystem',
            'PlayerListContent' => '§c§lChoose Player',
            'InfoUIContent' => "§bInformation: \nDay: §a{day} \n§bHour: §a{hour} \n§bMinute: §a{minute} \n§bSecond: §a{second} \n§bReason: §a{reason}",
            'InfoUIUnBanButton' => '§aUnban',
            'EnderPearlCooldownStart' => '§dNeptune§f » §aEnderpearl cooldown started!',
            'EnderPearlCooldownEnd' => '§dNeptune§f » §aEnderpearl cooldown ended!',
        )))->getAll();
        PracticeCore::getInstance()->BanData = new SQLite3(PracticeCore::getInstance()->getDataFolder() . 'Ban.db');
        PracticeCore::getInstance()->BanData->exec('CREATE TABLE IF NOT EXISTS banPlayers(player TEXT PRIMARY KEY, banTime INT, reason TEXT, staff TEXT);');
    }

    private function registerCommands(): void
    {
        Server::getInstance()->getCommandMap()->register('hub', new HubCommand());
        Server::getInstance()->getCommandMap()->register('tban', new TbanCommand());
        Server::getInstance()->getCommandMap()->register('tcheck', new TcheckCommand());
        Server::getInstance()->getCommandMap()->register('tps', new TpsCommand());
        Server::getInstance()->getCommandMap()->register('core', new PracticeCommand());
        Server::getInstance()->getCommandMap()->register('Restart', new RestartCommand());
        Server::getInstance()->getCommandMap()->register('broadcast', new BroadcastCommand());
        Server::getInstance()->getCommandMap()->register('pinfo', new PlayerInfoCommand());
        Server::getInstance()->getCommandMap()->register('settag', new SetTagCommand());
    }

    private function registerEvents(): void
    {
        new PracticeListener();
    }

    private function registerTasks(): void
    {
        PracticeCore::getInstance()->getScheduler()->scheduleRepeatingTask(new PracticeTask(), 1);
    }

    private function registerEntitys(): void
    {
        EntityFactory::getInstance()->register(FallingWool::class, function (World $world, CompoundTag $nbt): FallingWool {
            return new FallingWool(EntityDataHelper::parseLocation($nbt, $world), VanillaBlocks::WOOL(), $nbt);
        }, ['CustomFallingWoolBlock', 'minecraft:fallingwool']);
        EntityFactory::getInstance()->register(KillLeaderboard::class, function (World $world, CompoundTag $nbt): KillLeaderboard {
            return new KillLeaderboard(EntityDataHelper::parseLocation($nbt, $world), KillLeaderboard
                ::parseSkinNBT($nbt), $nbt);
        }, ['KillLeaderboard']);
        EntityFactory::getInstance()->register(DeathLeaderboard::class, function (World $world, CompoundTag $nbt): DeathLeaderboard {
            return new DeathLeaderboard(EntityDataHelper::parseLocation($nbt, $world), DeathLeaderboard
                ::parseSkinNBT($nbt), $nbt);
        }, ['DeathLeaderboard']);
        EntityFactory::getInstance()->register(BaseLeaderboard::class, function (World $world, CompoundTag $nbt): BaseLeaderboard {
            return new BaseLeaderboard(EntityDataHelper::parseLocation($nbt, $world), BaseLeaderboard
                ::parseSkinNBT($nbt), $nbt);
        }, ['BaseLeaderboard']);
        EntityFactory::getInstance()->register(PracticeBot::class, function (World $world, CompoundTag $nbt): PracticeBot {
            return new PracticeBot(EntityDataHelper::parseLocation($nbt, $world), PracticeBot
                ::parseSkinNBT($nbt), $nbt);
        }, ['practicebot']);
        EntityFactory::getInstance()->register(EnderPearlEntity::class, function (World $world, CompoundTag $nbt): EnderPearlEntity {
            return new EnderPearlEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['NThrownEnderpearl', 'neptune:ender_pearl'], EntityLegacyIds::ENDER_PEARL);
        EntityFactory::getInstance()->register(ArrowEntity::class, function (World $world, CompoundTag $nbt): ArrowEntity {
            return new ArrowEntity(EntityDataHelper::parseLocation($nbt, $world), null, true, $nbt);
        }, ['NArrow', 'neptune:arrow'], EntityLegacyIds::ARROW);
    }

    private function registerGenerators(): void
    {
        GeneratorManager::getInstance()->addGenerator(SumoGenerator::class, 'sumo', fn() => null);
    }

    public function SkillCooldown(Player $player): void
    {
        if (($player instanceof PracticePlayer) && $player->isSkillCooldown()) {
            PracticeCore::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                if ($player->getArmorInventory()->getHelmet()->getId() === ItemIds::SKULL) {
                    $player->getArmorInventory()->setHelmet(VanillaItems::AIR());
                }
                $player->sendMessage(PracticeCore::getInstance()->MessageData['SkillCleared']);
                $player->setSkillCooldown(false);
            }), PracticeConfig::SkillCooldownDelay);
        }
    }

    /**
     * @throws Exception
     */
    public function DeathReset(Player $player, PracticePlayer $dplayer, $arena = null): void
    {
        if ($dplayer->isAlive()) {
            if ($arena === PracticeCore::getArenaFactory()->getOITCArena()) {
                if ($dplayer->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getOITCArena())) {
                    $dplayer->getInventory()->clearAll();
                    $dplayer->getArmorInventory()->clearAll();
                    $dplayer->setHealth(20);
                    $dplayer->getInventory()->setItem(0, VanillaItems::STONE_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1)));
                    $dplayer->getInventory()->setItem(1, VanillaItems::BOW()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 500))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                    $dplayer->getInventory()->setItem(8, VanillaItems::ARROW());
                }
            } elseif ($arena === PracticeCore::getArenaFactory()->getBuildArena()) {
                if ($dplayer->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBuildArena())) {
                    $dplayer->getInventory()->clearAll();
                    $dplayer->getArmorInventory()->clearAll();
                    $dplayer->setHealth(20);
                    try {
                        foreach (PracticeCore::getInstance()->KitData->get($dplayer->getName()) as $slot => $item) {
                            $dplayer->getInventory()->setItem($slot, Item::jsonDeserialize($item));
                        }
                    } catch (Throwable) {
                        $dplayer->getInventory()->setItem(0, VanillaItems::IRON_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        $dplayer->getInventory()->addItem(VanillaItems::GOLDEN_APPLE()->setCount(3));
                        $dplayer->getInventory()->addItem(VanillaItems::ENDER_PEARL()->setCount(2));
                        $dplayer->getInventory()->addItem(VanillaBlocks::WOOL()->asItem()->setCount(128));
                        $dplayer->getInventory()->addItem(VanillaBlocks::COBWEB()->asItem());
                        $dplayer->getInventory()->addItem(VanillaItems::SHEARS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                    }
                }
                $dplayer->getArmorInventory()->setHelmet(VanillaItems::IRON_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
                $dplayer->getArmorInventory()->setChestplate(VanillaItems::IRON_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
                $dplayer->getArmorInventory()->setLeggings(VanillaItems::IRON_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
                $dplayer->getArmorInventory()->setBoots(VanillaItems::IRON_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
            } elseif ($arena === PracticeCore::getArenaFactory()->getBoxingArena()) {
                if ($dplayer->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBoxingArena())) {
                    $dplayer->setHealth(20);
                }
            } elseif ($arena === PracticeCore::getArenaFactory()->getComboArena()) {
                if ($dplayer->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getComboArena())) {
                    $dplayer->getInventory()->clearAll();
                    $item = VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(3);
                    $dplayer->getInventory()->addItem($item);
                }
            }
        }
        foreach ([$dplayer, $player] as $p) {
            if ($p instanceof PracticePlayer) {
                $p->setCombat(false);
                $p->setLastDamagePlayer('Unknown');
            }
        }
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();
        $this->addDeath($player);
        $this->GiveLobbyItem($player);
        $this->addKill($dplayer);
        $this->handleStreak($dplayer, $player);
    }

    public function addDeath(Player $player): void
    {
        if (!isset(PracticeCore::getCaches()->DeathLeaderboard[$player->getName()])) {
            PracticeCore::getCaches()->DeathLeaderboard[$player->getName()] = 1;
        } else {
            PracticeCore::getCaches()->DeathLeaderboard[$player->getName()]++;
        }
        $this->getData($player->getName())->addDeath();
    }

    public function getData($name): DataManager
    {
        return new DataManager($name);
    }

    public function GiveLobbyItem(Player $player): void
    {
        $item = VanillaItems::DIAMOND_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item->setCustomName('§r§dPlay');
        $item2 = VanillaItems::CLOCK()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item2->setCustomName('§r§dSettings');
        $item3 = VanillaItems::GOLDEN_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item3->setCustomName('§r§dBot');
        $item4 = VanillaItems::IRON_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item4->setCustomName('§r§dDuel');
        $item6 = VanillaItems::COMPASS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item6->setCustomName('§r§dProfile');
        $player->getOffHandInventory()->clearAll();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->getInventory()->setItem(0, $item);
        $player->getInventory()->setItem(8, $item2);
        $player->getInventory()->setItem(1, $item4);
        $player->getInventory()->setItem(2, $item3);
        $player->getInventory()->setItem(4, $item6);
    }

    public function addKill(Player $player): void
    {
        $data = $this->getData($player->getName());
        if (!isset(PracticeCore::getCaches()->KillLeaderboard[$player->getName()])) {
            PracticeCore::getCaches()->KillLeaderboard[$player->getName()] = 1;
        } else {
            PracticeCore::getCaches()->KillLeaderboard[$player->getName()]++;
        }
        $data->addKill();
    }

    public function handleStreak(Player $player, Player $death): void
    {
        $killer = $this->getData($player->getName());
        $loser = $this->getData($death->getName());
        $oldStreak = $loser->getStreak();
        $newStreak = $killer->getStreak();
        if ($oldStreak > 10) {
            $death->sendMessage(PracticeCore::getPrefixCore() . '§r§aYour ' . $oldStreak . ' killstreak was ended by ' . $player->getName() . '!');
            $player->sendMessage(PracticeCore::getPrefixCore() . '§r§aYou have ended ' . $death->getName() . "'s " . $oldStreak . ' killstreak!');
        }
        if (is_int($newStreak / 10)) {
            Server::getInstance()->broadcastMessage(PracticeCore::getPrefixCore() . '§r§a' . $player->getName() . ' is on a ' . $newStreak . ' killstreak!');
        }
    }

    public function getChatFormat(Player $player, string $message): string
    {
        $name = $player->getName();
        if (PracticeCore::getInstance()->getPracticeUtils()->getData($name)->getTag() !== null && PracticeCore::getInstance()->getPracticeUtils()->getData($name)->getTag() !== '') {
            $nametag = PracticeCore::getInstance()->getPracticeUtils()->getData($name)->getRank() . '§a ' . $player->getDisplayName() . ' §f[' . PracticeCore::getInstance()->getPracticeUtils()->getData($name)->getTag() . '§f]' . '§r§a > §r' . $message;
        } else {
            $nametag = PracticeCore::getInstance()->getPracticeUtils()->getData($name)->getRank() . '§a ' . $player->getDisplayName() . '§r§a > §r' . $message;
        }
        return $nametag;
    }

    public function Disable(): void
    {
        foreach (PracticeCore::getCaches()->DuelMatch as $activeMatch) {
            PracticeCore::getDuelManager()->stopMatch($activeMatch);
        }
        PracticeCore::getDeleteBlockHandler()->RemoveAllBlock();
        $this->loadMap("BUild");
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            if (str_contains(mb_strtolower($world->getFolderName()), 'duel') || str_contains(mb_strtolower($world->getFolderName()), 'bot')) {
                $name = $world->getFolderName();
                Server::getInstance()->getWorldManager()->unloadWorld($world);
                $this->deleteDir(Server::getInstance()->getDataPath() . "worlds/$name");
            }
        }
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            foreach ($world->getEntities() as $entity) {
                if ($entity instanceof PracticeBot) {
                    $entity->close();
                }
            }
        }
    }

    public function loadMap(string $folderName, bool $justSave = false): ?World
    {
        if (!Server::getInstance()->getWorldManager()->isWorldGenerated($folderName)) {
            return null;
        }
        if (Server::getInstance()->getWorldManager()->isWorldLoaded($folderName)) {
            Server::getInstance()->getWorldManager()->unloadWorld(Server::getInstance()->getWorldManager()->getWorldByName($folderName));
            $this->deleteDir(Server::getInstance()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . $folderName);
        }
        $zipPath = PracticeCore::getInstance()->getDataFolder() . 'Maps' . DIRECTORY_SEPARATOR . $folderName . '.zip';
        if (!file_exists($zipPath)) {
            Server::getInstance()->getLogger()->error("Could not reload map ($folderName). File wasn't found, try save level in setup mode.");
            return null;
        }
        $zipArchive = new ZipArchive();
        $zipArchive->open($zipPath);
        $zipArchive->extractTo(Server::getInstance()->getDataPath() . 'worlds');
        $zipArchive->close();
        if ($justSave) {
            return null;
        }
        Server::getInstance()->getWorldManager()->loadWorld($folderName, true);
        return Server::getInstance()->getWorldManager()->getWorldByName($folderName);
    }

    public function deleteDir($dirPath): void
    {
        if (!is_dir($dirPath)) {
            return;
        }
        if (!str_ends_with($dirPath, '/')) {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    public static function getLogger(string $err): void
    {
        $e = new DiscordWebhookEmbed();
        $web = new DiscordWebhook(PracticeCore::getInstance()->getConfig()->get('Webhook'));
        $msg = new DiscordWebhookUtils();
        $e->setTitle('Server Logger');
        $e->setTimestamp(new Datetime());
        $e->setColor(0x00ff00);
        $e->setDescription($err);
        $msg->addEmbed($e);
        $web->send($msg);
    }
}