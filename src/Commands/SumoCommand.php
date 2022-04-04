<?php

declare(strict_types=1);

namespace Kohaku\Commands;

use Kohaku\Arena\SumoHandler;
use Kohaku\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class SumoCommand extends Command
{

    public function __construct()
    {
        parent::__construct("sumo", "NeptuneCore Sumo commands", null, ["sumo"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Loader::getPrefixCore() . "§cUsage: §7/sumo help");
            return;
        }
        if ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            switch ($args[0]) {
                case "help":
                    $sender->sendMessage("§aSumo commands:\n" .
                        "§7/sumo help : Displays list of Sumo commands\n" .
                        "§7/sumo make : Create Sumo arena\n" .
                        "§7/sumo delete : Remove Sumo arena\n" .
                        "§7/sumo set : Set Sumo arena\n" .
                        "§7/sumo arenas : Displays list of arenas\n");
                    break;
                case "make":
                    if (!isset($args[1])) {
                        $sender->sendMessage("§cUsage: §7/sumo make <arenaName>");
                        break;
                    }
                    if (isset(Loader::getInstance()->SumoArenas[$args[1]])) {
                        $sender->sendMessage(Loader::getPrefixCore() . "§cArena $args[1] already exists!");
                        break;
                    }
                    Loader::getInstance()->SumoArenas[$args[1]] = new SumoHandler([]);
                    $sender->sendMessage(Loader::getPrefixCore() . "§aArena $args[1] created!");
                    break;
                case "delete":
                    if (!isset($args[1])) {
                        $sender->sendMessage(Loader::getPrefixCore() . "§cUsage: §7/sumo delete <arenaName>");
                        break;
                    }
                    if (!isset(Loader::getInstance()->SumoArenas[$args[1]])) {
                        $sender->sendMessage(Loader::getPrefixCore() . "§cArena $args[1] was not found!");
                        break;
                    }
                    $arena = Loader::getInstance()->SumoArenas[$args[1]];
                    foreach ($arena->players as $player) {
                        $player->teleport(Loader::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                    }
                    if (is_file($file = Loader::getInstance()->getDataFolder() . "SumoArenas" . DIRECTORY_SEPARATOR . $args[1] . ".yml")) unlink($file);
                    unset(Loader::getInstance()->SumoArenas[$args[1]]);
                    $sender->sendMessage(Loader::getPrefixCore() . "§aArena removed!");
                    break;
                case "set":
                    if (!$sender instanceof Player) {
                        $sender->sendMessage(Loader::getPrefixCore() . "§cThis command can be used only in-game!");
                        break;
                    }
                    if (!isset($args[1])) {
                        $sender->sendMessage(Loader::getPrefixCore() . "§cUsage: §7/sumo set <arenaName>");
                        break;
                    }
                    if (isset(Loader::getInstance()->SumoSetup[$sender->getName()])) {
                        $sender->sendMessage(Loader::getPrefixCore() . "§cYou are already in setup mode!");
                        break;
                    }
                    if (!isset(Loader::getInstance()->SumoArenas[$args[1]])) {
                        $sender->sendMessage(Loader::getPrefixCore() . "§cArena $args[1] does not found!");
                        break;
                    }
                    $sender->sendMessage("§aYou joined the setup mode.\n" .
                        "§7- Use §lhelp §r§7to display available commands\n" .
                        "§7- or §ldone §r§7to leave setup mode");
                    Loader::getInstance()->SumoSetup[$sender->getName()] = Loader::getInstance()->SumoArenas[$args[1]];
                    break;
                case "arenas":
                    if (count(Loader::getInstance()->SumoArenas) === 0) {
                        $sender->sendMessage(Loader::getPrefixCore() . "§cThere are 0 arenas.");
                        break;
                    }
                    $list = "§7Arenas:\n";
                    foreach (Loader::getInstance()->SumoArenas as $name => $arena) {
                        if ($arena->setup) {
                            $list .= "§7- $name : §cdisabled\n";
                        } else {
                            $list .= "§7- $name : §aenabled\n";
                        }
                    }
                    $sender->sendMessage($list);
                    break;
                default:
                    $sender->sendMessage(Loader::getPrefixCore() . "§7/sumo help");
                    break;
            }
        } else {
            $sender->sendMessage(Loader::getPrefixCore() . "§cYou don't have permission to use this command.");
        }
    }
}
