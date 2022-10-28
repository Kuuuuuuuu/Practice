<?php /** @noinspection PhpUsageOfSilenceOperatorInspection */

declare(strict_types=1);

namespace Kuu;

use Exception;
use Kuu\Commands\BroadcastCommand;
use Kuu\Commands\HologramCommand;
use Kuu\Commands\HubCommand;
use Kuu\Commands\PlayerInfoCommand;
use Kuu\Commands\PracticeCommand;
use Kuu\Commands\RestartCommand;
use Kuu\Commands\SetTagCommand;
use Kuu\Commands\TbanCommand;
use Kuu\Commands\TcheckCommand;
use Kuu\Commands\TpsCommand;
use Kuu\Entity\ArrowEntity;
use Kuu\Entity\EnderPearlEntity;
use Kuu\Entity\Leaderboard\BaseLeaderboard;
use Kuu\Entity\Leaderboard\DeathLeaderboard;
use Kuu\Entity\Leaderboard\KillLeaderboard;
use Kuu\Entity\Leaderboard\ParkourLeaderboard;
use Kuu\Entity\PracticeBot;
use Kuu\Events\PracticeListener;
use Kuu\Items\CustomSplashPotion;
use Kuu\Items\EnderPearl;
use Kuu\Task\PracticeTask;
use Kuu\Utils\ChunkManager;
use Kuu\Utils\Generator\DuelGenerator;
use Kuu\Utils\Generator\SumoGenerator;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\PotionType;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use SQLite3;
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
        PracticeCore::getInstance()->getPracticeUtils()->onChunkGenerated($pos->getWorld(), (int)$player->getPosition()->getX() >> 4, (int)$player->getPosition()->getZ() >> 4, function () use ($player, $pos) {
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

    public function generateUUID(): string
    {
        $data = random_bytes(16);
        assert(strlen($data) === 16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function initialize(): void
    {
        $this->registerItems();
        $this->registerConfigs();
        $this->registerCommands();
        $this->registerEvents();
        $this->registerTasks();
        $this->registerEntitys();
        $this->registerGenerators();
        $check = glob(Server::getInstance()->getDataPath() . 'worlds/*');
        if (is_array($check)) {
            foreach ($check as $world) {
                $world = str_replace(Server::getInstance()->getDataPath() . 'worlds/', '', $world);
                if (Server::getInstance()->getWorldManager()->isWorldLoaded($world)) {
                    continue;
                }
                Server::getInstance()->getWorldManager()->loadWorld($world, true);
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
    }

    private function registerConfigs(): void
    {
        @mkdir(PracticeCore::getInstance()->getDataFolder() . 'data/');
        PracticeCore::getInstance()->saveResource('config.yml');
        PracticeCore::getInstance()->KitData = new Config(PracticeCore::getInstance()->getDataFolder() . 'KitData.json', Config::JSON);
        PracticeCore::getInstance()->MessageData = (new Config(PracticeCore::getInstance()->getDataFolder() . 'messages.yml', Config::YAML, [
            'StartCombat' => '§bNeptune§f » §r§aYou Started combat!',
            'StopCombat' => '§bNeptune§f » §r§aYou Cleared combat!',
            'CantUseeditkit' => "§bNeptune§f » §r§cYou can't use this command in editkit mode!",
            'CantUseWantCombat' => "§bNeptune§f » §r§cYou can't use this command in combat!",
            'BroadcastBanMessage' => "§f––––––––––––––––––––––––\n§ePlayer §f: §c{player}\n§eHas banned: §c{day}§eD §f| §c{hour}§eH §f| §c{minute}§eM\n§eReason: §c{reason}\n§f––––––––––––––––––––––––§f",
            'KickBanMessage' => "§fLunar\n§cYou Are Banned\n§6Reason : §f{reason}\n§6Unban At §f: §e{day} D §f| §e{hour} H §f| §e{minute} M",
            'LoginBanMessage' => "§fLunar\n§cYou Are Banned\n§6Reason : §f{reason}\n§6Unban At §f: §e{day} D §f| §e{hour} H §f| §e{minute} M",
            'BanMyself' => "§bNeptune§f » §cYou can't ban yourself",
            'NoBanPlayers' => '§bNeptune§f » §aNo ban players',
            'UnBanPlayer' => '§bNeptune§f » §b{player} §ahas been unban',
            'AutoUnBanPlayer' => '§bNeptune§f »» §a{player} Has Auto Unban Already!',
            'BanListTitle' => PracticeConfig::Server_Name . '§eBanSystem',
            'BanListContent' => '§c§lChoose player',
            'PlayerListTitle' => PracticeConfig::Server_Name . '§eBanSystem',
            'PlayerListContent' => '§c§lChoose Player',
            'InfoUIContent' => "§bInformation: \nDay: §a{day} \n§bHour: §a{hour} \n§bMinute: §a{minute} \n§bSecond: §a{second} \n§bReason: §a{reason}",
            'InfoUIUnBanButton' => '§aUnban',
        ]))->getAll();
        PracticeCore::getInstance()->BanData = new SQLite3(PracticeCore::getInstance()->getDataFolder() . 'Ban.db');
        PracticeCore::getInstance()->BanData->exec('CREATE TABLE IF NOT EXISTS banPlayers(player TEXT PRIMARY KEY, banTime INT, reason TEXT, staff TEXT);');
    }

    private function registerCommands(): void
    {
        $Command = [
            'hub' => HubCommand::class,
            'tban' => TbanCommand::class,
            'tcheck' => TcheckCommand::class,
            'tps' => TpsCommand::class,
            'practice' => PracticeCommand::class,
            'restart' => RestartCommand::class,
            'broadcast' => BroadcastCommand::class,
            'pinfo' => PlayerInfoCommand::class,
            'settag' => SetTagCommand::class,
            'hologram' => HologramCommand::class,
        ];
        foreach ($Command as $key => $value) {
            Server::getInstance()->getCommandMap()->register($key, new $value());
        }
    }

    private function registerEvents(): void
    {
        new PracticeListener();
    }

    private function registerTasks(): void
    {
        new PracticeTask();
    }

    private function registerEntitys(): void
    {
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
        EntityFactory::getInstance()->register(ParkourLeaderboard::class, function (World $world, CompoundTag $nbt): ParkourLeaderboard {
            return new ParkourLeaderboard(EntityDataHelper::parseLocation($nbt, $world), ParkourLeaderboard::parseSkinNBT($nbt), $nbt);
        }, ['ParkourLeaderboard']);
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
        $generator = [
            SumoGenerator::class => 'sumo',
            DuelGenerator::class => 'duel'
        ];
        foreach ($generator as $key => $value) {
            GeneratorManager::getInstance()->addGenerator($key, $value, fn() => null);
        }
    }

    public function handleStreak(PracticePlayer $player, PracticePlayer $death): void
    {
        $oldStreak = $death->getStreak();
        $newStreak = $player->getStreak();
        if ($oldStreak > 10) {
            $death->sendMessage(PracticeCore::getPrefixCore() . '§r§aYour ' . $oldStreak . ' killstreak was ended by ' . $player->getName() . '!');
            $player->sendMessage(PracticeCore::getPrefixCore() . '§r§aYou have ended ' . $death->getName() . "'s " . $oldStreak . ' killstreaks!');
        }
        if (is_int($newStreak / 10)) {
            Server::getInstance()->broadcastMessage(PracticeCore::getPrefixCore() . '§r§a' . $player->getName() . ' is on a ' . $newStreak . ' killstreaks!');
        }
    }

    public function dispose(): void
    {
        foreach (PracticeCore::getCaches()->DuelMatch as $activeMatch) {
            PracticeCore::getDuelManager()->stopMatch($activeMatch);
        }
        $this->loadMap('BUild');
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            if (str_contains(mb_strtolower($world->getFolderName()), 'duel') || str_contains(mb_strtolower($world->getFolderName()), 'bot')) {
                $name = $world->getFolderName();
                Server::getInstance()->getWorldManager()->unloadWorld($world);
                $this->deleteDir(Server::getInstance()->getDataPath() . "worlds/$name");
            }
        }
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            foreach ($world->getEntities() as $entity) {
                if (!$entity instanceof BaseLeaderboard) {
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
            $world = Server::getInstance()->getWorldManager()->getWorldByName($folderName);
            if ($world instanceof World) {
                Server::getInstance()->getWorldManager()->unloadWorld($world);
                $this->deleteDir(Server::getInstance()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . $folderName);
            }
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

    public function deleteDir(string $dirPath): void
    {
        if (!is_dir($dirPath)) {
            return;
        }
        if (!str_ends_with($dirPath, '/')) {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_dir($file)) {
                    $this->deleteDir($file);
                } else {
                    unlink($file);
                }
            }
        }
        rmdir($dirPath);
    }

    public static function getLogger(string $err): void
    {
        Server::getInstance()->getLogger()->error($err);
    }
}