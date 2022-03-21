<?php

declare(strict_types=1);

namespace Kohaku\Core\Utils;

use DateTime;
use Exception;
use JetBrains\PhpStorm\Pure;
use Kohaku\Core\Arena\SkywarsHandler;
use Kohaku\Core\Arena\SumoHandler;
use Kohaku\Core\Commands\BroadcastCommand;
use Kohaku\Core\Commands\CoreCommand;
use Kohaku\Core\Commands\HubCommand;
use Kohaku\Core\Commands\PlayerInfoCommand;
use Kohaku\Core\Commands\RestartCommand;
use Kohaku\Core\Commands\SkyWarsCommand;
use Kohaku\Core\Commands\SumoCommand;
use Kohaku\Core\Commands\TbanCommand;
use Kohaku\Core\Commands\TcheckCommand;
use Kohaku\Core\Commands\TpsCommand;
use Kohaku\Core\Entity\FallingWool;
use Kohaku\Core\Events\BaseListener;
use Kohaku\Core\Events\PlayerListener;
use Kohaku\Core\Loader;
use Kohaku\Core\Task\BroadcastTask;
use Kohaku\Core\Task\HorizonTask;
use Kohaku\Core\Task\ScoreboardTask;
use Kohaku\Core\Utils\DiscordUtils\DiscordWebhook;
use Kohaku\Core\Utils\DiscordUtils\DiscordWebhookEmbed;
use Kohaku\Core\Utils\DiscordUtils\DiscordWebhookUtils;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Location;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\raklib\RakLibInterface;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\World;
use SQLite3;

class ArenaUtils
{

    public static function generateFallingWoolBlock(Location $location): FallingWool
    {
        $fallingBlock = new FallingWool($location, BlockFactory::getInstance()->get(BlockLegacyIds::WOOL, rand(0, 15)));
        $fallingBlock->setMotion(new Vector3(-sin(mt_rand(1, 360) / 60 * M_PI), 0.95, cos(mt_rand(1, 360) / 60 * M_PI)));
        $fallingBlock->spawnToAll();
        return $fallingBlock;
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

    #[Pure] public static function getInstance(): ArenaUtils
    {
        return new ArenaUtils();
    }

    public static function getLogger(string $err)
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

    public function getPlayerControls(Player $player): string
    {
        if (!isset(Loader::getInstance()->PlayerControl[strtolower($player->getName())]) or Loader::getInstance()->PlayerControl[strtolower($player->getName())] == null) {
            return "Unknown";
        }
        return Loader::getInstance()->ControlList[Loader::getInstance()->PlayerControl[strtolower($player->getName())]];
    }

    public function getPlayerDevices(Player $player): string
    {
        if (!isset(Loader::getInstance()->PlayerDevice[strtolower($player->getName())]) or Loader::getInstance()->PlayerDevice[strtolower($player->getName())] == null) {
            return "Unknown";
        }
        return Loader::getInstance()->PlayerDevice[strtolower($player->getName())];
    }

    public function getPlayerOs(Player $player): string
    {
        if (!isset(Loader::getInstance()->PlayerOS[strtolower($player->getName())]) or Loader::getInstance()->PlayerOS[strtolower($player->getName())] == null) {
            return "Unknown";
        }
        return Loader::getInstance()->OSList[Loader::getInstance()->PlayerOS[strtolower($player->getName())]];
    }

    public function getToolboxCheck(Player $player): string
    {
        return Loader::getInstance()->ToolboxCheck[strtolower($player->getName())] ?? "Unknown";
    }

    public function DeviceCheck(Player $player): void
    {
        $username = $player->getName();
        $data = $player->getPlayerInfo()->getExtraData();
        if ($data["CurrentInputMode"] !== null and $data["DeviceOS"] !== null and $data["DeviceModel"] !== null) {
            Loader::getInstance()->PlayerControl[strtolower($username) ?? "Unknown"] = $data["CurrentInputMode"];
            Loader::getInstance()->PlayerOS[strtolower($username) ?? "Unknown"] = $data["DeviceOS"];
            Loader::getInstance()->PlayerDevice[strtolower($username) ?? "Unknown"] = $data["DeviceModel"];
        }
        Loader::getInstance()->ToolboxCheck[strtolower($username)] = "Normal";
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
            Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "§e" . $username . " §cUsing §aToolbox. Please Avoid that Player!");
            Loader::getInstance()->ToolboxCheck[strtolower($username)] = "Toolbox";
        }
    }

    public function calculateTime(int $time): string
    {
        return gmdate("i:s", $time);
    }

    public function randomSpawn(Player $p)
    {
        $x = $z = mt_rand(0, 15);
        $y = $p->getWorld()->getHighestBlockAt($p->getPosition()->getFloorX(), $p->getPosition()->getFloorZ() + 1);
        $p->teleport(new Vector3($x, $y + 10, $z));
    }

    public function Start()
    {
        $this->loadallworlds();
        $this->registerConfigs();
        $this->registerCommands();
        $this->registerEvents();
        $this->registerTasks();
        $this->registerEntity();
        foreach (Server::getInstance()->getNetwork()->getInterfaces() as $interface) {
            if ($interface instanceof RakLibInterface) {
                $interface->setPacketLimit(9999999999);
            }
        }
    }

    public function loadallworlds()
    {
        foreach (glob(Server::getInstance()->getDataPath() . "worlds/*") as $world) {
            $world = str_replace(Server::getInstance()->getDataPath() . "worlds/", "", $world);
            if (Server::getInstance()->getWorldManager()->isWorldLoaded($world)) {
                continue;
            }
            Server::getInstance()->getWorldManager()->loadWorld($world);
        }
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            $world->setTime(0);
            $world->stopTime();
        }
    }

    private function registerConfigs(): void
    {
        @mkdir(Loader::getInstance()->getDataFolder() . "data/");
        @mkdir(Loader::getInstance()->getDataFolder() . "players/");
        Loader::getInstance()->CapeData = new Config(Loader::getInstance()->getDataFolder() . "CapeData.yml", Config::YAML);
        Loader::getInstance()->saveResource("config.yml");
        Loader::getInstance()->message = (new Config(Loader::getInstance()->getDataFolder() . "messages.yml", Config::YAML, array(
            "StartCombat" => "§bHorizon§f » §r§aYou Started combat!",
            "AntiCheatName" => "§bGuardian §f» ",
            "CooldownMessage" => "§bHorizon§f » §r§cYou can't chat for {cooldown} seconds!",
            "StopCombat" => "§bHorizon§f » §r§aYou Cleared combat!",
            "StartSkillMessage" => "§bHorizon§f » §r§aYou Started Skill!",
            "NoPlayer" => "§bHorizon§f » §r§cPlayer not found!",
            "SkillCleared" => "§bHorizon§f » §r§aSkill Cleared!",
            "CantUseWantCombat" => "§bHorizon§f » §r§cYou can't use this command in combat!",
            "BroadcastBanMessage" => "§f––––––––––––––––––––––––\n§ePlayer §f: §c{player}\n§eHas banned: §c{day}§eD §f| §c{hour}§eH §f| §c{minute}§eM\n§eReason: §c{reason}\n§f––––––––––––––––––––––––§f",
            "KickBanMessage" => "§bGuardian\n§cYou Are Banned\n§6Reason : §f{reason}\n§6Unban At §f: §e{day} D §f| §e{hour} H §f| §e{minute} M",
            "LoginBanMessage" => "§bGuardian\n§cYou Are Banned\n§6Reason : §f{reason}\n§6Unban At §f: §e{day} D §f| §e{hour} H §f| §e{minute} M",
            "BanMyself" => "§bGuardian §f> §cYou can't ban yourself",
            "NoBanPlayers" => "§bGuardian §f> §aNo ban players",
            "UnBanPlayer" => "§bGuardian §f> §b{player} §ahas been unban",
            "AutoUnBanPlayer" => "§bGuardian §f> §a{player} Has Auto Unban Already!",
            "BanListTitle" => "§bHorizon §eBanSystem",
            "BanListContent" => "§c§lChoose player",
            "PlayerListTitle" => "§bHorizon §eBanSystem",
            "PlayerListContent" => "§c§lChoose Player",
            "InfoUIContent" => "§bInformation: \nDay: §a{day} \n§bHour: §a{hour} \n§bMinute: §a{minute} \n§bSecond: §a{second} \n§bReason: §a{reason}",
            "InfoUIUnBanButton" => "§aUnban",
        )))->getAll();
        Loader::getInstance()->db = new SQLite3(Loader::getInstance()->getDataFolder() . "Ban.db");
        Loader::getInstance()->db->exec("CREATE TABLE IF NOT EXISTS banPlayers(player TEXT PRIMARY KEY, banTime INT, reason TEXT, staff TEXT);");
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
        Server::getInstance()->getCommandMap()->register("skywars", new SkyWarsCommand());
    }

    private function registerEvents(): void
    {
        Server::getInstance()->getPluginManager()->registerEvents(new PlayerListener(), Loader::getInstance());
        Server::getInstance()->getPluginManager()->registerEvents(new BaseListener(), Loader::getInstance());
    }

    private function registerTasks(): void
    {
        Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new HorizonTask(), 1);
        Loader::getInstance()->getScheduler()->scheduleDelayedRepeatingTask(new BroadcastTask(), 200, 11000);
    }

    private function registerEntity(): void
    {
        EntityFactory::getInstance()->register(FallingWool::class,
            function (World $world, CompoundTag $nbt): FallingWool {
                return new FallingWool(
                    EntityDataHelper::parseLocation($nbt, $world),
                    BlockFactory::getInstance()->get(BlockLegacyIds::WOOL, 0),
                    $nbt
                );
            }, ['CustomFallingWoolBlock', 'minecraft:fallingwool']);
    }

    /**
     * @throws Exception
     */
    public function DeathReset(Player $player, Player $dplayer, $arena = null): void
    {
        $name = $player->getName();
        $dname = $dplayer->getName();
        if (isset(Loader::getInstance()->opponent[$name])) {
            unset(Loader::getInstance()->opponent[$name]);
        }
        if (isset(Loader::getInstance()->opponent[$dname])) {
            unset(Loader::getInstance()->opponent[$dname]);
        }
        if ($arena === "OITC") {
            unset(Loader::getInstance()->ArrowOITC[$dname]);
            unset(Loader::getInstance()->ArrowOITC[$name]);
            $dplayer->getInventory()->clearAll();
            $dplayer->getInventory()->setItem(1, ItemFactory::getInstance()->get(ItemIds::STONE_SWORD, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1)));
            $dplayer->getInventory()->setItem(0, ItemFactory::getInstance()->get(ItemIds::BOW, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 500))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
            $dplayer->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::ARROW, 0, 1));
        } else if ($arena === "Build") {
            $dplayer->getInventory()->clearAll();
            $dplayer->getArmorInventory()->clearAll();
            $item = ItemFactory::getInstance()->get(ItemIds::IRON_SWORD, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000));
            $dplayer->getInventory()->setItem(0, $item);
            $dplayer->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::GOLDEN_APPLE, 0, 3));
            $dplayer->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::ENDER_PEARL, 0, 2));
            $dplayer->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::SANDSTONE, 0, 128));
            $dplayer->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::COBWEB, 0, 1));
            $dplayer->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::DIAMOND_PICKAXE, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000)));
            $dplayer->getArmorInventory()->setHelmet(ItemFactory::getInstance()->get(ItemIds::IRON_HELMET, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
            $dplayer->getArmorInventory()->setChestplate(ItemFactory::getInstance()->get(ItemIds::IRON_CHESTPLATE, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
            $dplayer->getArmorInventory()->setLeggings(ItemFactory::getInstance()->get(ItemIds::IRON_LEGGINGS, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
            $dplayer->getArmorInventory()->setBoots(ItemFactory::getInstance()->get(ItemIds::IRON_BOOTS, 0, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
        } else if ($arena === "Boxing") {
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $this->addKill($dplayer);
            $this->addDeath($player);
            $this->GiveItem($player);
            $this->handleStreak($dplayer, $player);
            $player->getEffects()->clear();
            if (isset(Loader::getInstance()->CombatTimer[$name])) {
                unset(Loader::getInstance()->CombatTimer[$name]);
            }
            if (isset(Loader::getInstance()->CombatTimer[$dname])) {
                Loader::getInstance()->CombatTimer[$dname] = 0.5;
            }
            if (isset(Loader::getInstance()->BoxingPoint[$dname])) {
                unset(Loader::getInstance()->BoxingPoint[$dname]);
            }
            if (isset(Loader::getInstance()->BoxingPoint[$name])) {
                unset(Loader::getInstance()->BoxingPoint[$name]);
            }
        } else {
            if (isset(Loader::getInstance()->CombatTimer[$name])) {
                unset(Loader::getInstance()->CombatTimer[$name]);
            }
            if (isset(Loader::getInstance()->CombatTimer[$dname])) {
                unset(Loader::getInstance()->CombatTimer[$dname]);
            }
            if (isset(Loader::getInstance()->SkillCooldown[$name])) {
                unset(Loader::getInstance()->SkillCooldown[$name]);
            }
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $this->ReItem($dplayer);
            $this->addDeath($player);
            $this->GiveItem($player);
            $this->addKill($dplayer);
            $this->handleStreak($dplayer, $player);
        }
    }

    public function addKill(Player $player)
    {
        $data = $this->getData($player->getName());
        $data->addKill();
    }

    public function getData($name): PlayerData
    {
        return new PlayerData($name);
    }

    public function addDeath(Player $player)
    {
        $this->getData($player->getName())->addDeath();
    }

    public function GiveItem(Player $player)
    {
        $item = ItemFactory::getInstance()->get(279, 0, 1);
        $item->setCustomName("§r§bPlay");
        $item2 = ItemFactory::getInstance()->get(286, 0, 1);
        $item2->setCustomName("§r§bSettings");
        $player->getInventory()->setItem(4, $item);
        $player->getInventory()->setItem(8, $item2);
    }

    public function handleStreak(Player $player, Player $death)
    {
        $killer = $this->getData($player->getName());
        $loser = $this->getData($death->getName());
        $oldStreak = $loser->getStreak();
        if ($oldStreak >= 5) {
            $death->sendMessage(Loader::getPrefixCore() . "§r§aYour " . $oldStreak . " killstreak was ended by " . $player->getName() . "!");
            $player->sendMessage(Loader::getPrefixCore() . "§r§aYou have ended " . $death->getName() . "'s " . $oldStreak . " killstreak!");
        }
        $newStreak = $killer->getStreak();
        if (is_int($newStreak / 5)) {
            Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "§r§a" . $player->getName() . " is on a " . $newStreak . " killstreak!");
        }
    }

    public function ReItem(Player $player)
    {
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getComboArena())) {
            $player->getInventory()->clearAll();
            $item = ItemFactory::getInstance()->get(466, 0, 3);
            $player->getInventory()->addItem($item);
        }
    }

    public function lazy(Player $player)
    {
        Loader::getinstance()->getScheduler()->scheduleRepeatingTask(new ScoreboardTask($player), 35);
    }

    public function JoinRandomArenaSumo(Player $player)
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
            if ($arena->phase !== 0 || $arena->setup) {
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

    public function JoinRandomArenaSkywars(Player $player)
    {
        $arena = $this->getRandomSkyWarsArenas();
        if (!is_null($arena)) {
            $arena->joinToArena($player);
            return;
        }
        $player->sendMessage(Loader::getPrefixCore() . "§e All the arenas are full!");
    }

    public function getRandomSkyWarsArenas(): ?SkywarsHandler
    {
        $availableArenas = [];
        foreach (Loader::getInstance()->SkywarArenas as $index => $arena) {
            $availableArenas[$index] = $arena;
        }
        foreach ($availableArenas as $index => $arena) {
            if ($arena->phase !== 0 || $arena->setup) {
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
        return Loader::getInstance()->SkywarArenas[$availableArenas[array_rand($availableArenas)]];
    }
}