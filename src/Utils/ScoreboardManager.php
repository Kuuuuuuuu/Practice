<?php

namespace Kohaku\Utils;

use Kohaku\NeptunePlayer;
use Kohaku\Loader;
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
        $on = count(Server::getInstance()->getOnlinePlayers());
        $lines = [
            1 => "§7---------------§7",
            2 => "§dOnline§f: §a$on §dPing§f: §a$ping",
            4 => "§a",
            5 => "§dK§f: §a$kills §dD§f: §a$deaths",
            6 => "§dKDR§f: §a$rate §dElo§f: §a{$data->getElo()}",
            7 => "§e",
            8 => "§dIn-Queue §a$queue",
            9 => "§7---------------"
        ];
        Loader::getScoreboardUtils()->new($player, "ObjectiveName", Loader::getScoreboardTitle());
        foreach ($lines as $line => $content) {
            Loader::getScoreboardUtils()->setLine($player, $line, $content);
        }
    }

    public function getQueuePlayer(): int
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

    public function Boxing(Player $player): void
    {
        $name = $player->getName();
        $boxingp = Loader::getInstance()->BoxingPoint[$name] ?? 0;
        $opponent = Loader::getInstance()->PlayerOpponent[$name] ?? "";
        $opponentboxingp = Loader::getInstance()->BoxingPoint[$opponent] ?? 0;
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