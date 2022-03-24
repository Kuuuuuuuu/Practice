<?php

namespace Kohaku\Core\Utils;

use Kohaku\Core\Loader;
use pocketmine\{entity\effect\EffectInstance,
    entity\effect\VanillaEffects,
    math\Vector3,
    player\Player,
    scheduler\ClosureTask,
    Server
};

class EventManager
{
    public function removePlayer(string $string)
    {
        if (($key = array_search($string, Loader::getInstance()->PlayersEvent)) !== false) {
            unset(Loader::getInstance()->PlayersEvent[$key]);
        }
    }

    public function roundOver(Player $player, Player $player2)
    {
        $winner = null;
        $loser = null;
        if (!Loader::getInstance()->EventRound) return;
        $world = Loader::getInstance()->getConfig()->get("EventWorld");
        if (in_array($player->getName(), Loader::getInstance()->PlayersEvent)) {
            $winner = $player;
            $loser = $player2;
        } else if (in_array($player2->getName(), Loader::getInstance()->PlayersEvent)) {
            $winner = $player2;
            $loser = $player;
        }
        if ($winner->getWorld()->getFolderName() === $world) {
            $winner->teleport(Server::getInstance()->getWorldManager()->getWorldByName($world)->getSafeSpawn());
            Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . $winner->getName() . " §awon the match against§e " . $loser->getName());
            Loader::getInstance()->EventRound = false;
        }
        if (count(Loader::getInstance()->PlayersEvent) > 1) {
            list($red, $blue) = array_chunk(Loader::getInstance()->PlayersEvent, ceil(count(Loader::getInstance()->PlayersEvent) / 2));
        } else {
            Loader::$event->endRound();
            return;
        }
        if (Loader::getInstance()->EventRound) {
            return;
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
        Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player1, $player2): void {
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
    }

    public function endRound()
    {
        if (Loader::getInstance()->EventStarted) {
            if (count(Loader::getInstance()->PlayersEvent) <= 1) {
                $winner = Loader::getInstance()->PlayersEvent[array_key_first(Loader::getInstance()->PlayersEvent)];
                Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "§6The event is now over. The winner is §a" . "$winner!");
            } else {
                Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "§eThe event is over. A winner could not be determined");
            }
            $world = Loader::getInstance()->getConfig()->get("EventWorld");
            foreach (Server::getInstance()->getWorldManager()->getWorldByName($world)->getEntities() as $players) {
                if ($players instanceof Player) {
                    $players->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
                    ArenaUtils::getInstance()->GiveItem($players);
                }
            }
        }
        Loader::getInstance()->EventArena = false;
        Loader::getInstance()->EventStarted = false;
        Loader::getInstance()->PlayersEvent = [];
        Loader::getInstance()->EventRoundCount = 0;
        Loader::getInstance()->EventRound = false;
    }

    public function removeFighting(string $string)
    {
        if (($key = array_search($string, Loader::getInstance()->EventFighting)) !== false) {
            unset(Loader::getInstance()->EventFighting[$key]);
        }
    }

    public function Kit(Player $player)
    {
        $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 99999, 10, false));
    }
}