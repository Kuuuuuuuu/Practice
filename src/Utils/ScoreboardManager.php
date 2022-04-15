<?php

namespace Kohaku\Utils;

use Kohaku\Loader;
use Kohaku\NeptunePlayer;
use pocketmine\player\Player;
use pocketmine\Server;

class ScoreboardManager
{

    public function sb(Player $player): void
    {
        $ping = $player->getNetworkSession()->getPing();
        $data = Loader::getInstance()->getArenaUtils()->getData($player->getName());
        $kills = $data->getKills();
        $rate = round($data->getKdr(), 2);
        $deaths = $data->getDeaths();
        $queue = $this->getQueuePlayer();
        $duel = $this->getDuelPlayer();
        $on = count(Server::getInstance()->getOnlinePlayers());
        $lines = [
            1 => "§7---------------§7",
            2 => "§dOnline§f: §a$on",
            3 => "§dPing§f: §a$ping",
            4 => "§a",
            5 => "§dK§f: §a$kills §dD§f: §a$deaths",
            6 => "§dKDR§f: §a$rate §dElo§f: §a{$data->getElo()}",
            7 => "§e",
            8 => "§dIn-Queue §a$queue",
            9 => "§dIn-Duel §a$duel",
            10 => "§7---------------"
        ];
        Loader::getScoreboardUtils()->new($player, "ObjectiveName", Loader::getScoreboardTitle());
        foreach ($lines as $line => $content) {
            Loader::getScoreboardUtils()->setLine($player, $line, $content);
        }
    }

    private function getQueuePlayer(): int
    {
        $queue = 0;
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player instanceof NeptunePlayer) {
                if ($player->isInQueue()) {
                    $queue += 1;
                }
            }
        }
        return $queue ?? 0;
    }

    private function getDuelPlayer(): int
    {
        $duel = 0;
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player instanceof NeptunePlayer) {
                if ($player->isDueling()) {
                    $duel += 1;
                }
            }
        }
        return $duel ?? 0;
    }

    public function sb2(Player $player): void
    {
        $ping = $player->getNetworkSession()->getPing();
        $server = Server::getInstance();
        $on = count(Server::getInstance()->getOnlinePlayers());
        $lines = [
            1 => "§7---------------§0",
            2 => "§dOnline§f: §a$on",
            3 => "§dPing§f: §a$ping",
            4 => "§dTPS§f: §a{$server->getTicksPerSecond()} ({$server->getTickUsage()})",
            5 => "§7---------------"
        ];
        Loader::getScoreboardUtils()->new($player, "ObjectiveName", Loader::getScoreboardTitle());
        foreach ($lines as $line => $content) {
            Loader::getScoreboardUtils()->setLine($player, $line, $content);
        }
    }

    public function Boxing(NeptunePlayer $player): void
    {
        $boxingp = $player->BoxingPoint;
        $opponent = $player->getOpponent();
        if ($opponent !== null) {
            $oppopl = Server::getInstance()->getPlayerByPrefix($opponent);
            /** @var NeptunePlayer $oppopl */
            $opponentboxingp = $oppopl->BoxingPoint;
        } else {
            $opponentboxingp = 0;
        }
        $lines = [
            1 => "§7---------------§0",
            2 => "§dYour§f: §a$boxingp",
            3 => "§dOpponent§f: §c$opponentboxingp",
            4 => "§7---------------"
        ];
        Loader::getScoreboardUtils()->new($player, "ObjectiveName", Loader::getScoreboardTitle());
        foreach ($lines as $line => $content) {
            Loader::getScoreboardUtils()->setLine($player, $line, $content);
        }
    }
}