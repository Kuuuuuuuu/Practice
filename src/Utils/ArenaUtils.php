<?php

declare(strict_types=1);

namespace Kohaku\Utils;

use DateTime;
use Exception;
use JsonException;
use Kohaku\Arena\SumoHandler;
use Kohaku\Commands\BroadcastCommand;
use Kohaku\Commands\CoreCommand;
use Kohaku\Commands\HubCommand;
use Kohaku\Commands\PlayerInfoCommand;
use Kohaku\Commands\RestartCommand;
use Kohaku\Commands\SetTagCommand;
use Kohaku\Commands\SumoCommand;
use Kohaku\Commands\TbanCommand;
use Kohaku\Commands\TcheckCommand;
use Kohaku\Commands\TpsCommand;
use Kohaku\Entity\ArrowEntity;
use Kohaku\Entity\DeathLeaderboard;
use Kohaku\Entity\EnderPearlEntity;
use Kohaku\Entity\FallingWool;
use Kohaku\Entity\FishingHook;
use Kohaku\Entity\FistBot;
use Kohaku\Entity\KillLeaderboard;
use Kohaku\Entity\ParkourLeaderboard;
use Kohaku\Events\AntiCheatListener;
use Kohaku\Events\NeptuneListener;
use Kohaku\Items\Bow;
use Kohaku\Items\EnderPearl;
use Kohaku\Items\FishingRod;
use Kohaku\Loader;
use Kohaku\NeptunePlayer;
use Kohaku\SkyWars\Skywars;
use Kohaku\Task\NeptuneTask;
use Kohaku\Utils\DiscordUtils\DiscordWebhook;
use Kohaku\Utils\DiscordUtils\DiscordWebhookEmbed;
use Kohaku\Utils\DiscordUtils\DiscordWebhookUtils;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Location;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\raklib\RakLibInterface;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\World;
use SQLite3;
use Throwable;
use ZipArchive;

class ArenaUtils
{

    public static function generateFallingWoolBlock(Location $location): FallingWool
    {
        $fallingBlock = new FallingWool($location, VanillaBlocks::WOOL()->setColor(self::randomDyeColor()));
        $fallingBlock->setMotion(new Vector3(-sin(mt_rand(1, 360) / 60 * M_PI), 0.95, cos(mt_rand(1, 360) / 60 * M_PI)));
        $fallingBlock->spawnToAll();
        return $fallingBlock;
    }

    public static function randomDyeColor(): DyeColor
    {
        $array = [
            DyeColor::BLACK(),
            DyeColor::BLUE(),
            DyeColor::BROWN(),
            DyeColor::CYAN(),
            DyeColor::GRAY(),
            DyeColor::GREEN(),
            DyeColor::LIGHT_BLUE(),
            DyeColor:: LIGHT_GRAY(),
            DyeColor::LIME(),
            DyeColor::MAGENTA(),
            DyeColor::ORANGE(),
            DyeColor::PINK(),
            DyeColor::PURPLE(),
            DyeColor::RED(),
            DyeColor::WHITE(),
            DyeColor::YELLOW()
        ];
        $size = count($array) - 1;
        return $array[mt_rand(0, $size)];
    }

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

    public static function onChunkGenerated(World $world, int $x, int $z, callable $callable): void
    {
        if ($world->isChunkPopulated($x, $z)) {
            ($callable)();
            return;
        }
        $world->registerChunkLoader(new ChunkManager($world, $x, $z, $callable), $x, $z);
    }

    public static function randomColor($excludedColors = []): string
    {
        $array = [
            TextFormat::DARK_PURPLE => true,
            TextFormat::GOLD => true,
            TextFormat::RED => true,
            TextFormat::GREEN => true,
            TextFormat::LIGHT_PURPLE => true,
            TextFormat::AQUA => true,
            TextFormat::DARK_RED => true,
            TextFormat::DARK_AQUA => true,
            TextFormat::BLUE => true,
            TextFormat::GRAY => true,
            TextFormat::DARK_GREEN => true,
            TextFormat::BLACK => true,
            TextFormat::DARK_BLUE => true,
            TextFormat::DARK_GRAY => true,
            TextFormat::YELLOW => true,
            TextFormat::WHITE => true
        ];
        $array2 = $array;
        foreach ($excludedColors as $c) {
            if (isset($array[$c]))
                unset($array[$c]);
        }
        if (count($array) === 0) $array = $array2;
        $size = count($array) - 1;
        $keys = array_keys($array);
        return (string)$keys[mt_rand(0, $size)];
    }

    /**
     * @throws Exception
     */

    function generateUUID()
    {
        $data = random_bytes(16);
        assert(strlen($data) == 16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function DeviceCheck(Player $player): void
    {
        $username = $player->getName();
        $data = $player->getPlayerInfo()->getExtraData();
        if ($player instanceof NeptunePlayer) {
            if ($data["CurrentInputMode"] !== null) $player->PlayerControl = Loader::getInstance()->ControlList[$data["CurrentInputMode"]];
            if ($data["DeviceOS"] !== null) $player->PlayerOS = Loader::getInstance()->OSList[$data["DeviceOS"]];
            if ($data["DeviceModel"] !== null) $player->PlayerDevice = $data["DeviceModel"];
            $deviceOS = (int)$data["DeviceOS"];
            $deviceModel = (string)$data["DeviceModel"];
            if ($deviceOS !== 1) {
                return;
            }
            $name = explode(" ", $deviceModel);
            if (!isset($name[0])) {
                return;
            }
            $check = strtoupper($name[0]);
            if ($check !== $name[0]) {
                Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "§e" . $username . " §cMight be Using §aToolbox. Please Avoid that Player!");
                $player->ToolboxStatus = "Toolbox";
            }
        }
    }

    public function spawnLightning(Player $player, Player $damager): void
    {
        $pos = $player->getPosition();
        $light2 = AddActorPacket::create(Entity::nextRuntimeId(), 1, "minecraft:lightning_bolt", $player->getPosition()->asVector3(), null, $player->getLocation()->getYaw(), $player->getLocation()->getPitch(), 0.0, [], [], []);
        $block = $player->getWorld()->getBlock($player->getPosition()->floor()->down());
        $particle = new BlockBreakParticle($block);
        $player->getWorld()->addParticle($pos, $particle, $player->getWorld()->getPlayers());
        $sound2 = PlaySoundPacket::create("ambient.weather.thunder", $pos->getX(), $pos->getY(), $pos->getZ(), 1, 1);
        Server::getInstance()->broadcastPackets([$player, $damager], [$light2, $sound2]);
    }

    public function calculateTime(int $time): string
    {
        return gmdate("i:s", $time);
    }

    public function randomSpawn(Player $p): void
    {
        $x = $z = mt_rand(0, 15);
        $y = $p->getWorld()->getHighestBlockAt($p->getPosition()->getFloorX(), $p->getPosition()->getFloorZ() + 1);
        $p->teleport(new Vector3($x, $y + 10, $z));
    }

    public function Enable(): void
    {
        Loader::getInstance()->getLogger()->info("\n\n\n              [" . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Neptune" . TextFormat::WHITE . "]\n\n");
        Server::getInstance()->getNetwork()->setName("§dNeptune §fNetwork");
        $this->registerItems();
        $this->registerConfigs();
        $this->registerGenerator();
        $this->registerCommands();
        $this->registerEvents();
        $this->registerTasks();
        $this->registerEntity();
        $this->loadallworlds();
        foreach (Server::getInstance()->getNetwork()->getInterfaces() as $interface) {
            if ($interface instanceof RakLibInterface) {
                $interface->setPacketLimit(9999999999);
            }
        }
    }

    public static function getLogger(string $err): void
    {
        $e = new DiscordWebhookEmbed();
        $web = new DiscordWebhook(Loader::getInstance()->getConfig()->get("api"));
        $msg = new DiscordWebhookUtils();
        $e->setTitle("Error");
        $e->setFooter("Made By KohakuChan");
        $e->setTimestamp(new Datetime());
        $e->setColor(0x00ff00);
        $e->setDescription("Error: " . $err);
        $msg->addEmbed($e);
        $web->send($msg);
    }

    private function registerItems(): void
    {
        ItemFactory::getInstance()->register(new EnderPearl(new ItemIdentifier(ItemIds::ENDER_PEARL, 0), "Ender Pearl"), true);
        ItemFactory::getInstance()->register(new Bow(new ItemIdentifier(ItemIds::BOW, 0), "Bow"), true);
        ItemFactory::getInstance()->register(new FishingRod(new ItemIdentifier(ItemIds::FISHING_ROD, 0), "Fishing Rod"), true);
    }

    private function registerConfigs(): void
    {
        @mkdir(Loader::getInstance()->getDataFolder() . "data/");
        @mkdir(Loader::getInstance()->getDataFolder() . "players/");
        @mkdir(Loader::getInstance()->getDataFolder() . "Kits/");
        Loader::getInstance()->ArtifactData = new Config(Loader::getInstance()->getDataFolder() . "ArtifactData.yml", Config::YAML);
        Loader::getInstance()->CapeData = new Config(Loader::getInstance()->getDataFolder() . "CapeData.yml", Config::YAML);
        Loader::getInstance()->saveResource("config.yml");
        Loader::getInstance()->MessageData = (new Config(Loader::getInstance()->getDataFolder() . "messages.yml", Config::YAML, array(
            "StartCombat" => "§dNeptune§f » §r§aYou Started combat!",
            "AntiCheatName" => "§fLunar §f» ",
            "CooldownMessage" => "§dNeptune§f » §r§cYou can't chat for {cooldown} seconds!",
            "StopCombat" => "§dNeptune§f » §r§aYou Cleared combat!",
            "StartSkillMessage" => "§dNeptune§f » §r§aYou Started Skill!",
            "NoPlayer" => "§dNeptune§f » §r§cPlayer not found!",
            "SkillCleared" => "§dNeptune§f » §r§aSkill Cleared!",
            "CantUseWantCombat" => "§dNeptune§f » §r§cYou can't use this command in combat!",
            "BroadcastBanMessage" => "§f––––––––––––––––––––––––\n§ePlayer §f: §c{player}\n§eHas banned: §c{day}§eD §f| §c{hour}§eH §f| §c{minute}§eM\n§eReason: §c{reason}\n§f––––––––––––––––––––––––§f",
            "KickBanMessage" => "§fLunar\n§cYou Are Banned\n§6Reason : §f{reason}\n§6Unban At §f: §e{day} D §f| §e{hour} H §f| §e{minute} M",
            "LoginBanMessage" => "§fLunar\n§cYou Are Banned\n§6Reason : §f{reason}\n§6Unban At §f: §e{day} D §f| §e{hour} H §f| §e{minute} M",
            "BanMyself" => "§dNeptune§f » §cYou can't ban yourself",
            "NoBanPlayers" => "§dNeptune§f » §aNo ban players",
            "UnBanPlayer" => "§dNeptune§f » §b{player} §ahas been unban",
            "AutoUnBanPlayer" => "§dNeptune§f »» §a{player} Has Auto Unban Already!",
            "BanListTitle" => "§dNeptune §eBanSystem",
            "BanListContent" => "§c§lChoose player",
            "PlayerListTitle" => "§dNeptune §eBanSystem",
            "PlayerListContent" => "§c§lChoose Player",
            "InfoUIContent" => "§bInformation: \nDay: §a{day} \n§bHour: §a{hour} \n§bMinute: §a{minute} \n§bSecond: §a{second} \n§bReason: §a{reason}",
            "InfoUIUnBanButton" => "§aUnban",
        )))->getAll();
        Loader::getInstance()->BanData = new SQLite3(Loader::getInstance()->getDataFolder() . "Ban.db");
        Loader::getInstance()->BanData->exec("CREATE TABLE IF NOT EXISTS banPlayers(player TEXT PRIMARY KEY, banTime INT, reason TEXT, staff TEXT);");
    }

    public function registerGenerator(): void
    {
        GeneratorManager::getInstance()->addGenerator(DuelGenerator::class, "Duel", fn() => null);
    }

    private function registerCommands(): void
    {
        Server::getInstance()->getCommandMap()->register("hub", new HubCommand());
        Server::getInstance()->getCommandMap()->register("tban", new TbanCommand());
        Server::getInstance()->getCommandMap()->register("tcheck", new TcheckCommand());
        Server::getInstance()->getCommandMap()->register("tps", new TpsCommand());
        Server::getInstance()->getCommandMap()->register("core", new CoreCommand());
        Server::getInstance()->getCommandMap()->register("Restart", new RestartCommand());
        Server::getInstance()->getCommandMap()->register("sumo", new SumoCommand());
        Server::getInstance()->getCommandMap()->register("broadcast", new BroadcastCommand());
        Server::getInstance()->getCommandMap()->register("pinfo", new PlayerInfoCommand());
        Server::getInstance()->getCommandMap()->register("settag", new SetTagCommand());
    }

    private function registerEvents(): void
    {
        Server::getInstance()->getPluginManager()->registerEvents(new NeptuneListener(), Loader::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new AntiCheatListener(), Loader::getInstance());
    }

    private function registerTasks(): void
    {
        Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new NeptuneTask(), 1);
    }

    private function registerEntity(): void
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
        EntityFactory::getInstance()->register(ParkourLeaderboard::class, function (World $world, CompoundTag $nbt): ParkourLeaderboard {
            return new ParkourLeaderboard(EntityDataHelper::parseLocation($nbt, $world), ParkourLeaderboard
                ::parseSkinNBT($nbt), $nbt);
        }, ['ParkourLeaderboard']);
        EntityFactory::getInstance()->register(FistBot::class, function (World $world, CompoundTag $nbt): FistBot {
            return new FistBot(EntityDataHelper::parseLocation($nbt, $world), FistBot
                ::parseSkinNBT($nbt), $nbt);
        }, ['practicebot']);
        EntityFactory::getInstance()->register(EnderPearlEntity::class, function (World $world, CompoundTag $nbt): EnderPearlEntity {
            return new EnderPearlEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['HThrownEnderpearl', 'horizon:ender_pearl'], EntityLegacyIds::ENDER_PEARL);
        EntityFactory::getInstance()->register(ArrowEntity::class, function (World $world, CompoundTag $nbt): ArrowEntity {
            return new ArrowEntity(EntityDataHelper::parseLocation($nbt, $world), null, true, $nbt);
        }, ['HArrow', 'horizon:arrow'], EntityLegacyIds::ARROW);
        EntityFactory::getInstance()->register(FishingHook::class, function (World $world, CompoundTag $nbt): FishingHook {
            return new FishingHook(EntityDataHelper::parseLocation($nbt, $world), null, null);
        }, ['HHook', 'horizon:hook'], EntityLegacyIds::FISHING_HOOK);
    }

    public function loadallworlds(): void
    {
        foreach (glob(Server::getInstance()->getDataPath() . "worlds/*") as $world) {
            $world = str_replace(Server::getInstance()->getDataPath() . "worlds/", "", $world);
            if (Server::getInstance()->getWorldManager()->isWorldLoaded($world)) {
                continue;
            }
            Server::getInstance()->getWorldManager()->loadWorld($world, true);
        }
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            $world->setTime(0);
            $world->stopTime();
        }
    }

    public function SkillCooldown(NeptunePlayer $player): void
    {
        if ($player->isSkillCooldown()) {
            Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                if ($player->getArmorInventory()->getHelmet()->getId() === ItemIds::SKULL) {
                    $player->getArmorInventory()->setHelmet(VanillaItems::AIR());
                }
                $player->sendMessage(Loader::getInstance()->MessageData["SkillCleared"]);
                $player->setSkillCooldown(false);
            }), 250);
        }
    }

    /**
     * @throws Exception
     */
    public function DeathReset(NeptunePlayer $player, NeptunePlayer $dplayer, $arena = null): void
    {
        if ($dplayer->isAlive()) {
            if ($arena === Loader::getArenaFactory()->getOITCArena()) {
                if ($dplayer->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getOITCArena())) {
                    $dplayer->getInventory()->clearAll();
                    $dplayer->getArmorInventory()->clearAll();
                    $dplayer->setHealth(20);
                    $dplayer->getInventory()->setItem(0, VanillaItems::STONE_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1)));
                    $dplayer->getInventory()->setItem(1, VanillaItems::BOW()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 500))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                    $dplayer->getOffHandInventory()->setItem(0, VanillaItems::ARROW());
                }
            } elseif ($arena === Loader::getArenaFactory()->getBuildArena()) {
                if ($dplayer->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBuildArena())) {
                    $dplayer->getInventory()->clearAll();
                    $dplayer->getArmorInventory()->clearAll();
                    $dplayer->setHealth(20);
                    $kit = $this->getKitContains($dplayer, "build");
                    if ($kit !== null) {
                        $dplayer->getInventory()->setContents($kit);
                    } else {
                        $dplayer->getInventory()->setItem(0, VanillaItems::IRON_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        $dplayer->getInventory()->addItem(VanillaItems::GOLDEN_APPLE()->setCount(3)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        $dplayer->getInventory()->addItem(VanillaItems::ENDER_PEARL()->setCount(2)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        $dplayer->getInventory()->addItem(VanillaBlocks::WOOL()->asItem()->setCount(128)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        $dplayer->getInventory()->addItem(VanillaBlocks::COBWEB()->asItem()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        $dplayer->getInventory()->addItem(VanillaItems::SHEARS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                    }
                }
                $dplayer->getArmorInventory()->setHelmet(VanillaItems::IRON_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
                $dplayer->getArmorInventory()->setChestplate(VanillaItems::IRON_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
                $dplayer->getArmorInventory()->setLeggings(VanillaItems::IRON_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
                $dplayer->getArmorInventory()->setBoots(VanillaItems::IRON_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
            } elseif ($arena === Loader::getArenaFactory()->getBoxingArena()) {
                if ($dplayer->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBoxingArena())) {
                    $dplayer->setHealth(20);
                }
            } elseif ($arena === Loader::getArenaFactory()->getComboArena()) {
                if ($dplayer->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getComboArena())) {
                    $dplayer->getInventory()->clearAll();
                    $item = ItemFactory::getInstance()->get(466, 0, 3);
                    $dplayer->getInventory()->addItem($item);
                }
            }
        }
        foreach ([$dplayer, $player] as $p) {
            $p->CombatTime = 0.5;
            $p->setOpponent(null);
            $p->setSkillCooldown(false);
            if ($p instanceof NeptunePlayer) {
                $p->setLastDamagePlayer("Unknown");
            }
        }
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();
        $this->addDeath($player);
        $this->GiveItem($player);
        $this->addKill($dplayer);
        $this->handleStreak($dplayer, $player);
    }

    public function getKitContains(NeptunePlayer $player, string $mode): ?array
    {
        $config = $this->getKitData($player);
        $contents = $config->get(mb_strtolower($mode));
        return $contents !== false ? $contents : null;
    }

    private function getKitData(NeptunePlayer $player): Config
    {
        return new Config(Loader::getInstance()->getDataFolder() . "Kits/" . strtolower($player->getName()) . ".sl", Config::SERIALIZED);
    }

    public function addDeath(Player $player): void
    {
        if (!isset(Loader::getInstance()->DeathLeaderboard[$player->getName()])) {
            Loader::getInstance()->DeathLeaderboard[$player->getName()] = 1;
        } else {
            Loader::getInstance()->DeathLeaderboard[$player->getName()]++;
        }
        $this->getData($player->getName())->addDeath();
    }

    public function getData($name): DataManager
    {
        return new DataManager($name);
    }

    public function GiveItem(Player $player): void
    {
        $item = VanillaItems::GOLDEN_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item->setCustomName("§r§dPlay");
        $item2 = VanillaItems::IRON_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item2->setCustomName("§r§dSettings");
        $item3 = VanillaItems::DIAMOND_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item3->setCustomName("§r§dBot");
        $item4 = VanillaItems::BOOK()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item4->setCustomName("§r§dDuel");
        $player->getOffHandInventory()->clearAll();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->getInventory()->setItem(0, $item);
        $player->getInventory()->setItem(8, $item2);
        $player->getInventory()->setItem(4, $item3);
        $player->getInventory()->setItem(1, $item4);
    }

    public function addKill(Player $player): void
    {
        $data = $this->getData($player->getName());
        if (!isset(Loader::getInstance()->KillLeaderboard[$player->getName()])) {
            Loader::getInstance()->KillLeaderboard[$player->getName()] = 1;
        } else {
            Loader::getInstance()->KillLeaderboard[$player->getName()]++;
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
            $death->sendMessage(Loader::getPrefixCore() . "§r§aYour " . $oldStreak . " killstreak was ended by " . $player->getName() . "!");
            $player->sendMessage(Loader::getPrefixCore() . "§r§aYou have ended " . $death->getName() . "'s " . $oldStreak . " killstreak!");
        }
        if (is_int($newStreak / 10)) {
            Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "§r§a" . $player->getName() . " is on a " . $newStreak . " killstreak!");
        }
    }

    public function JoinRandomArenaSumo(Player $player): void
    {
        $arena = $this->getRandomSumoArenas();
        if (!is_null($arena)) {
            $arena->joinToArena($player);
            return;
        }
        $player->sendMessage(Loader::getPrefixCore() . "§e All the arenas are full!");
    }

    public function getRandomSumoArenas(): ?SumoHandler
    {
        $availableArenas = [];
        foreach (Loader::getInstance()->SumoArenas as $index => $arena) {
            $availableArenas[$index] = $arena;
        }
        foreach ($availableArenas as $index => $arena) {
            if ($arena->phase !== 0 or $arena->setup or count($arena->players) >= 2) {
                unset($availableArenas[$index]);
            }
        }
        $arenasByPlayers = [];
        foreach ($availableArenas as $index => $arena) {
            $arenasByPlayers[$index] = count($arena->players);
        }
        arsort($arenasByPlayers);
        $top = -1;
        $availableArenas = [];
        foreach ($arenasByPlayers as $index => $players) {
            if ($top === -1) {
                $top = $players;
                $availableArenas[] = $index;
            } else {
                if ($top === $players) {
                    $availableArenas[] = $index;
                }
            }
        }
        if (empty($availableArenas)) {
            return null;
        }
        return Loader::getInstance()->SumoArenas[$availableArenas[array_rand($availableArenas)]];
    }

    public function getChatFormat(Player $player, string $message): string
    {
        $name = $player->getName();
        if (Loader::getInstance()->getArenaUtils()->getData($name)->getTag() !== null and Loader::getInstance()->getArenaUtils()->getData($name)->getTag() !== "") {
            $nametag = Loader::getInstance()->getArenaUtils()->getData($name)->getRank() . "§a " . $player->getDisplayName() . " §f[" . Loader::getInstance()->getArenaUtils()->getData($name)->getTag() . "§f]" . "§r§a > §r" . $message;
        } else {
            $nametag = Loader::getInstance()->getArenaUtils()->getData($name)->getRank() . "§a " . $player->getDisplayName() . "§r§a > §r" . $message;
        }
        return $nametag;
    }

    /**
     * @throws JsonException
     */
    public function Disable(): void
    {
        foreach (Loader::getDuelManager()->getMatches() as $activeMatch => $matchTask) {
            Loader::getDuelManager()->stopMatch($activeMatch);
        }
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            if (str_contains(mb_strtolower($world->getFolderName()), "duel")) {
                $name = $world->getFolderName();
                Server::getInstance()->getWorldManager()->unloadWorld($world);
                $this->deleteDir(Server::getInstance()->getDataPath() . "worlds/$name");
            }
        }
        Loader::getInstance()->getLogger()->info(TextFormat::RED . "Disable Yeet");
        $this->loadMap("BUild");
        $this->killbot();
    }

    public function deleteDir($dirPath): bool
    {
        if (!is_dir($dirPath)) {
            return false;
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
        return true;
    }

    public function loadMap(string $folderName, bool $justSave = false): ?World
    {
        if (!Server::getInstance()->getWorldManager()->isWorldGenerated($folderName)) {
            return null;
        }
        if (Server::getInstance()->getWorldManager()->isWorldLoaded($folderName)) {
            Server::getInstance()->getWorldManager()->unloadWorld(Server::getInstance()->getWorldManager()->getWorldByName($folderName));
            $this->deleteDir(Server::getInstance()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $folderName);
        }
        $zipPath = Loader::getInstance()->getDataFolder() . "Maps" . DIRECTORY_SEPARATOR . $folderName . ".zip";
        if (!file_exists($zipPath)) {
            Server::getInstance()->getLogger()->error("Could not reload map ($folderName). File wasn't found, try save level in setup mode.");
            return null;
        }
        $zipArchive = new ZipArchive();
        $zipArchive->open($zipPath);
        $zipArchive->extractTo(Server::getInstance()->getDataPath() . "worlds");
        $zipArchive->close();
        if ($justSave) {
            return null;
        }
        Server::getInstance()->getWorldManager()->loadWorld($folderName, true);
        return Server::getInstance()->getWorldManager()->getWorldByName($folderName);
    }

    public function killbot(): void
    {
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            foreach ($world->getEntities() as $entity) {
                if ($entity instanceof FistBot) {
                    $entity->close();
                }
            }
        }
    }

    public function saveKitContains(NeptunePlayer $player, string $mode): bool
    {
        try {
            $config = $this->getKitData($player);
            $config->set($mode, $player->getInventory()->getContents(true));
            $config->save();
            return true;
        } catch (Throwable) {
            return false;
        }
    }
}