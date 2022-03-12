<?php

declare(strict_types=1);

namespace Kohaku\Core\Commands;

use Kohaku\Core\Arena\SumoHandler;
use Kohaku\Core\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class SumoCommand extends Command
{

    public function __construct()
    {
        parent::__construct("sumo", "HorizonCore Sumo commands", null, ["sumo"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage("§cUsage: §7/sumo help");
            return;
        }
        switch ($args[0]) {
            case "help":
                if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    break;
                }
                $sender->sendMessage("§aSumo commands:\n" .
                    "§7/sumo help : Displays list of Sumo commands\n" .
                    "§7/sumo make : Create Sumo arena\n" .
                    "§7/sumo delete : Remove Sumo arena\n" .
                    "§7/sumo set : Set Sumo arena\n" .
                    "§7/sumo arenas : Displays list of arenas\n");
                break;
            case "make":
                if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    break;
                }
                if (!isset($args[1])) {
                    $sender->sendMessage("§cUsage: §7/sumo make <arenaName>");
                    break;
                }
                if (isset(Loader::getInstance()->SumoArena[$args[1]])) {
                    $sender->sendMessage("§cArena $args[1] already exists!");
                    break;
                }
                Loader::getInstance()->SumoArena[$args[1]] = new SumoHandler(Loader::getInstance(), []);
                $sender->sendMessage("§aArena $args[1] created!");
                break;
            case "delete":
                if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    break;
                }
                if (!isset($args[1])) {
                    $sender->sendMessage("§cUsage: §7/sumo delete <arenaName>");
                    break;
                }
                if (!isset(Loader::getInstance()->SumoArena[$args[1]])) {
                    $sender->sendMessage("§cArena $args[1] was not found!");
                    break;
                }
                $arena = Loader::getInstance()->SumoArena[$args[1]];
                foreach ($arena->players as $player) {
                    $player->teleport(Loader::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                }
                if (is_file($file = Loader::getInstance()->getDataFolder() . "arenasumo" . DIRECTORY_SEPARATOR . $args[1] . ".yml")) unlink($file);
                unset(Loader::getInstance()->SumoArena[$args[1]]);
                $sender->sendMessage("§aArena removed!");
                break;
            case "set":
                if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    break;
                }
                if (!$sender instanceof Player) {
                    $sender->sendMessage("§cThis command can be used only in-game!");
                    break;
                }
                if (!isset($args[1])) {
                    $sender->sendMessage("§cUsage: §7/sumo set <arenaName>");
                    break;
                }
                if (isset(Loader::getInstance()->SumoSetup[$sender->getName()])) {
                    $sender->sendMessage("§cYou are already in setup mode!");
                    break;
                }
                if (!isset(Loader::getInstance()->SumoArena[$args[1]])) {
                    $sender->sendMessage("§cArena $args[1] does not found!");
                    break;
                }
                $sender->sendMessage("§aYou joined the setup mode.\n" .
                    "§7- Use §lhelp §r§7to display available commands\n" .
                    "§7- or §ldone §r§7to leave setup mode");
                Loader::getInstance()->SumoSetup[$sender->getName()] = Loader::getInstance()->SumoArena[$args[1]];
                break;
            case "arenas":
                if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    $sender->sendMessage("§cYou do not have permissions to use this command!");
                    break;
                }
                if (count(Loader::getInstance()->SumoArena) === 0) {
                    $sender->sendMessage("§cThere are 0 arenas.");
                    break;
                }
                $list = "§7Arenas:\n";
                foreach (Loader::getInstance()->SumoArena as $name => $arena) {
                    if ($arena->setup) {
                        $list .= "§7- $name : §cdisabled\n";
                    } else {
                        $list .= "§7- $name : §aenabled\n";
                    }
                }
                $sender->sendMessage($list);
                break;
            default:
                if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    $sender->sendMessage("§cYou do not have permissions to use this command!");
                    break;
                }
                $sender->sendMessage("§cUsage: §7/sumo help");
                break;
        }
    }
}
