<?php

declare(strict_types=1);

namespace Kohaku\Core\Commands;

use Kohaku\Core\Arena\SkywarsHandler;
use Kohaku\Core\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class SkyWarsCommand extends Command
{

    public function __construct()
    {
        parent::__construct("skywars", "SkyWars commands", null, ["sw"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Loader::getPrefixCore() . "§cUsage: §7/sw help");
            return;
        }
        if ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            switch ($args[0]) {
                case "help":
                    $sender->sendMessage(Loader::getPrefixCore() . "SkyWars commands:\n" .
                        "§7/sw help : Displays list of SkyWars commands\n" .
                        "§7/sw create : Create SkyWars arena\n" .
                        "§7/sw remove : Remove SkyWars arena\n" .
                        "§7/sw set : Set SkyWars arena\n" .
                        "§7/sw arenas : Displays list of arenas");

                    break;
                case "create":
                    if (!isset($args[1])) {
                        $sender->sendMessage(Loader::getPrefixCore() . "§cUsage: §7/sw create <arenaName>");
                        break;
                    }
                    if (isset(Loader::getInstance()->SkywarArenas[$args[1]])) {
                        $sender->sendMessage(Loader::getPrefixCore() . "$args[1] already exists!");
                        break;
                    }
                    Loader::getInstance()->SkywarArenas[$args[1]] = new SkywarsHandler(Loader::getInstance(), []);
                    $sender->sendMessage(Loader::getPrefixCore() . "$args[1] created!");
                    break;
                case "remove":
                    if (!isset($args[1])) {
                        $sender->sendMessage(Loader::getPrefixCore() . "§cUsage: §7/sw remove <arenaName>");
                        break;
                    }
                    if (!isset(Loader::getInstance()->SkywarArenas[$args[1]])) {
                        $sender->sendMessage(Loader::getPrefixCore() . "$args[1] was not found!");
                        break;
                    }
                    /** @var SkywarsHandler $arena */
                    $arena = Loader::getInstance()->SkywarArenas[$args[1]];
                    foreach ($arena->players as $player) {
                        $player->teleport(Loader::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                    }
                    if (is_file($file = Loader::getInstance()->getDataFolder() . "SkywarsArenas" . DIRECTORY_SEPARATOR . $args[1] . ".yml")) unlink($file);
                    unset(Loader::getInstance()->SkywarArenas[$args[1]]);
                    $sender->sendMessage(Loader::getPrefixCore() . "removed!");
                    break;
                case "set":
                    if (!$sender instanceof Player) {
                        $sender->sendMessage(Loader::getPrefixCore() . "This command can be used only in-game!");
                        break;
                    }
                    if (!isset($args[1])) {
                        $sender->sendMessage(Loader::getPrefixCore() . "§cUsage: §7/sw set <arenaName>");
                        break;
                    }
                    if (isset(Loader::getInstance()->SkywarSetup[$sender->getName()])) {
                        $sender->sendMessage(Loader::getPrefixCore() . "You are already in setup mode!");
                        break;
                    }
                    if (!isset(Loader::getInstance()->SkywarArenas[$args[1]])) {
                        $sender->sendMessage(Loader::getPrefixCore() . "$args[1] does not found!");
                        break;
                    }
                    $sender->sendMessage(Loader::getPrefixCore() . "You are joined setup mode.\n" .
                        "§7- use §lhelp §r§7to display available commands\n" .
                        "§7- or §ldone §r§7to leave setup mode");
                    Loader::getInstance()->SkywarSetup[$sender->getName()] = Loader::getInstance()->SkywarArenas[$args[1]];
                    break;
                case "arenas":
                    if (count(Loader::getInstance()->SkywarArenas) === 0) {
                        $sender->sendMessage(Loader::getPrefixCore() . "There are 0 arenas.");
                        break;
                    }
                    $list = "§7> Arenas:\n";
                    foreach (Loader::getInstance()->SkywarArenas as $name => $arena) {
                        if ($arena->setup) {
                            $list .= "§7- $name : §cdisabled\n";
                        } else {
                            $list .= "§7- $name : §aenabled\n";
                        }
                    }
                    $sender->sendMessage($list);
                    break;
                default:
                    $sender->sendMessage(Loader::getPrefixCore() . "§cUsage: §7/sw help");
                    break;
            }
        } else {
            $sender->sendMessage(Loader::getPrefixCore() . "§cYou don't have permission to use this command.");
        }
    }
}
