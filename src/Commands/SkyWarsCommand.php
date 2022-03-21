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
            $sender->sendMessage("§cUsage: §7/sw help");
            return;
        }
        if ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            switch ($args[0]) {
                case "help":
                    $sender->sendMessage("§a> SkyWars commands:\n" .
                        "§7/sw help : Displays list of SkyWars commands\n" .
                        "§7/sw create : Create SkyWars arena\n" .
                        "§7/sw remove : Remove SkyWars arena\n" .
                        "§7/sw set : Set SkyWars arena\n" .
                        "§7/sw arenas : Displays list of arenas");

                    break;
                case "create":
                    if (!isset($args[1])) {
                        $sender->sendMessage("§cUsage: §7/sw create <arenaName>");
                        break;
                    }
                    if (isset(Loader::getInstance()->arenas[$args[1]])) {
                        $sender->sendMessage("§c> SkywarsHandler $args[1] already exists!");
                        break;
                    }
                    Loader::getInstance()->SkywarArenas[$args[1]] = new SkywarsHandler(Loader::getInstance(), []);
                    $sender->sendMessage("§a> SkywarsHandler $args[1] created!");
                    break;
                case "remove":
                    if (!$sender->hasPermission("sw.cmd.remove")) {
                        $sender->sendMessage("§cYou have not permissions to use this command!");
                        break;
                    }
                    if (!isset($args[1])) {
                        $sender->sendMessage("§cUsage: §7/sw remove <arenaName>");
                        break;
                    }
                    if (!isset(Loader::getInstance()->arenas[$args[1]])) {
                        $sender->sendMessage("§c> SkywarsHandler $args[1] was not found!");
                        break;
                    }
                    /** @var SkywarsHandler $arena */
                    $arena = Loader::getInstance()->arenas[$args[1]];
                    foreach ($arena->players as $player) {
                        $player->teleport(Loader::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                    }
                    if (is_file($file = Loader::getInstance()->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR . $args[1] . ".yml")) unlink($file);
                    unset(Loader::getInstance()->arenas[$args[1]]);
                    $sender->sendMessage("§a> SkywarsHandler removed!");
                    break;
                case "set":
                    if (!$sender instanceof Player) {
                        $sender->sendMessage("§c> This command can be used only in-game!");
                        break;
                    }
                    if (!isset($args[1])) {
                        $sender->sendMessage("§cUsage: §7/sw set <arenaName>");
                        break;
                    }
                    if (isset(Loader::getInstance()->setters[$sender->getName()])) {
                        $sender->sendMessage("§c> You are already in setup mode!");
                        break;
                    }
                    if (!isset(Loader::getInstance()->arenas[$args[1]])) {
                        $sender->sendMessage("§c> SkywarsHandler $args[1] does not found!");
                        break;
                    }
                    $sender->sendMessage("§a> You are joined setup mode.\n" .
                        "§7- use §lhelp §r§7to display available commands\n" .
                        "§7- or §ldone §r§7to leave setup mode");
                    Loader::getInstance()->SumoSetup[$sender->getName()] = Loader::getInstance()->arenas[$args[1]];
                    break;
                case "arenas":
                    if (count(Loader::getInstance()->SkywarArenas) === 0) {
                        $sender->sendMessage("§6> There are 0 arenas.");
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
                    if (!$sender->hasPermission("sw.cmd.help")) {
                        $sender->sendMessage("§cYou have not permissions to use this command!");
                        break;
                    }
                    $sender->sendMessage("§cUsage: §7/sw help");
                    break;
            }
        }
    }
}
