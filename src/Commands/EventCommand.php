<?php

namespace Kohaku\Core\Commands;

use Kohaku\Core\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;

class EventCommand extends Command
{

    public function __construct()
    {
        parent::__construct(
            "event",
            "Event HorizonCore Commands",
            "/event help",
            ["ev"]
        );
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Loader::getPrefixCore() . "Usage: /event <create:start:round:join:leave:spectate:end:list>");
            return true;
        }
        switch (strtolower($args[0])) {
            case "create":
                if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    $sender->sendMessage(Loader::getPrefixCore() . "You do not have permission to use this command!");
                    return true;
                }
                if (Loader::getInstance()->EventArena) {
                    $sender->sendMessage(Loader::getPrefixCore() . "There is already a event going on!");
                    return true;
                }
                Loader::getInstance()->EventArena = true;
                $sender->sendMessage(Loader::getPrefixCore() . "You have created a event!");
                Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . $sender->getName() . " has started a event!");
                break;
            case "start":
                if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    $sender->sendMessage(Loader::getPrefixCore() . "You do not have permission to use this command!");
                    return true;
                }
                if (Loader::getInstance()->EventStarted) {
                    $sender->sendMessage(Loader::getPrefixCore() . "The event has already been started!");
                    return true;
                }
                if (!Loader::getInstance()->EventArena) {
                    $sender->sendMessage(Loader::getPrefixCore() . "There is currently no Event going on!");
                    return true;
                }
                if (count(Loader::getInstance()->PlayersEvent) <= 1) {
                    $sender->sendMessage(Loader::getPrefixCore() . "There are not enough participants to start the event!");
                    return true;
                }
                Loader::getInstance()->EventStarted = true;
                $sender->sendMessage(Loader::getPrefixCore() . "The event has been started!");
                foreach (Loader::getInstance()->PlayersEvent as $participant) {
                    $world = Loader::getInstance()->getConfig()->get("EventWorld");
                    if (!Server::getInstance()->getWorldManager()->isWorldLoaded($world)) {
                        Server::getInstance()->getWorldManager()->loadWorld($world);
                    }
                    $player = Server::getInstance()->getPlayerByPrefix($participant);
                    $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName($world)->getSafeSpawn());
                }
                break;
            case "round":
                if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    $sender->sendMessage(Loader::getPrefixCore() . "You do not have permission to use this command!");
                    return true;
                }
                if (!Loader::getInstance()->EventArena) {
                    $sender->sendMessage(Loader::getPrefixCore() . "There is currently no Event going on!");
                    return true;
                }
                if (!Loader::getInstance()->EventStarted) {
                    $sender->sendMessage(Loader::getPrefixCore() . "The event has not been started!");
                    return true;
                }
                if (count(Loader::getInstance()->PlayersEvent) > 1) {
                    list($red, $blue) = array_chunk(Loader::getInstance()->PlayersEvent, ceil(count(Loader::getInstance()->PlayersEvent) / 2));
                } else {
                    Loader::$event->endRound();
                    return true;
                }
                if (Loader::getInstance()->EventRound) {
                    $sender->sendMessage(Loader::getPrefixCore() . "There is already a round going on!");
                    return true;
                }
                Loader::getInstance()->EventRound = true;
                $player1 = Server::getInstance()->getPlayerByPrefix($red[array_rand($red)]);
                $player2 = Server::getInstance()->getPlayerByPrefix($blue[array_rand($blue)]);
                Loader::getInstance()->EventRoundCount++;
                $p1 = $player1->getName();
                $p2 = $player2->getName();
                Loader::getInstance()->EventFighting[] = $p1;
                Loader::getInstance()->EventFighting[] = $p2;
                $rn = Loader::getInstance()->EventRoundCount;
                $world = Loader::getInstance()->getConfig()->get("EventWorld");
                $worldd = Server::getInstance()->getWorldManager()->getWorldByName($world);
                Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "$p1" . " §ais fighting§e " . "$p2!" . " §6Round $rn");
                $player1->teleport($worldd->getSafeSpawn());
                $player2->teleport($worldd->getSafeSpawn());
                $pos = Loader::getInstance()->getConfig()->get("pos");
                $pos2 = Loader::getInstance()->getConfig()->get("pos2");
                $player1->teleport(new Vector3($pos[0], $pos[1], $pos[2]));
                $player2->teleport(new Vector3($pos2[0], $pos2[1], $pos2[2]));
                Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(
                    function () use ($player1, $player2): void {
                        if (!in_array($player1->getName(), Loader::getInstance()->EventFighting)) {
                            Loader::$event->roundOver($player1, $player2);
                            Loader::$event->removeFighting($player2->getName());
                            return;
                        }
                        if (!in_array($player2->getName(), Loader::getInstance()->EventFighting)) {
                            Loader::$event->roundOver($player2, $player1);
                            Loader::$event->removeFighting($player1->getName());
                            return;
                        }
                        foreach ([$player1, $player2] as $players) {
                            Loader::$event->Kit($players);
                        }
                    }
                ), 200);
                break;
            case "join":
                if (!Loader::getInstance()->EventArena) {
                    $sender->sendMessage(Loader::getPrefixCore() . "There is currently no Event going on!");
                    return true;
                }
                if (Loader::getInstance()->EventStarted) {
                    $sender->sendMessage(Loader::getPrefixCore() . "The event has already started!");
                    return true;
                }
                if (!in_array($sender->getName(), Loader::getInstance()->PlayersEvent)) {
                    Loader::getInstance()->PlayersEvent[] = $sender->getName();
                    $world = Loader::getInstance()->getConfig()->get("EventWorld");
                    $worldd = Server::getInstance()->getWorldManager()->getWorldByName($world);
                    $sender->teleport($worldd->getSafeSpawn());
                    $sender->sendMessage(Loader::getPrefixCore() . "You have joined the event!");
                } else {
                    $sender->sendMessage(Loader::getPrefixCore() . "You are already in the event!");
                }
                break;
            case "leave":
                if (!Loader::getInstance()->EventArena) {
                    $sender->sendMessage(Loader::getPrefixCore() . "There is currently no Event going on!");
                    return true;
                }
                if (in_array($sender->getName(), Loader::getInstance()->PlayersEvent)) {
                    if (!Loader::getInstance()->EventStarted) {
                        Loader::$event->removePlayer($sender->getName());
                        $sender->sendMessage(Loader::getPrefixCore() . "You have left the event!");
                    } else {
                        $sender->sendMessage(Loader::getPrefixCore() . "The event has already started!");
                    }
                } else {
                    $sender->sendMessage(Loader::getPrefixCore() . "You are not in the event!");
                }
                break;
            case "spectate":
                if (!Loader::getInstance()->EventArena) {
                    $sender->sendMessage(Loader::getPrefixCore() . "There is currently no Event going on!");
                    return true;
                }
                if (!Loader::getInstance()->EventStarted) {
                    $sender->sendMessage(Loader::getPrefixCore() . "The event has not been started!");
                    return true;
                }
                $world = Loader::getInstance()->getConfig()->get("EventWorld");
                /* @var $player Player */
                if ($sender->getWorld()->getName() === $world) {
                    $sender->sendMessage(Loader::getPrefixCore() . "You are already in the world!");
                    return true;
                }
                $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($world)->getSafeSpawn());
                break;
            case "end":
                if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    $sender->sendMessage(Loader::getPrefixCore() . "You do not have permission to use this command!");
                    return true;
                }
                if (!Loader::getInstance()->EventArena) {
                    $sender->sendMessage(Loader::getPrefixCore() . "There is currently no Event going on!");
                    return true;
                }
                Loader::$event->endRound();
                $sender->sendMessage(Loader::getPrefixCore() . "You have ended the event!");
                break;
            case "list":
                if (!Loader::getInstance()->EventArena) {
                    $sender->sendMessage(Loader::getPrefixCore() . "There is currently no Event going on!");
                    return true;
                }
                if (count(Loader::getInstance()->PlayersEvent) == 0) {
                    $sender->sendMessage(Loader::getPrefixCore() . "There are currently no participants!");
                    return true;
                }
                $pp = rtrim(implode(", ", Loader::getInstance()->PlayersEvent), ",");
                $sender->sendMessage(TF::DARK_GREEN . "Participants: " . Loader::getPrefixCore() . "$pp");
                break;
            default:
                $sender->sendMessage(Loader::getPrefixCore() . "Usage: /event <create:start:round:join:leave:spectate:end:list>");
        }
        return true;
    }
}