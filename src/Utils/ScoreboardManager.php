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
        $server = Server::getInstance();
        $data = Loader::getInstance()->getArenaUtils()->getData($player->getName());
        $kills = $data->getKills();
        $rate = round($data->getKdr(), 2);
        $deaths = $data->getDeaths();
        $queue = $this->getQueuePlayer();
        $on = count(Server::getInstance()->getOnlinePlayers());
        $lines = [
            1 => "§7---------------§7",
            2 => "§bOnline§f: §6$on",
            3 => "§d",
            4 => "§bPing§f: §6$ping",
            5 => "§bTPS§f: §a{$server->getTicksPerSecond()} ({$server->getTickUsage()})",
            6 => "§a",
            7 => "§bK§f: §6$kills",
            8 => "§bD§f: §6$deaths",
            9 => "§bK/D§f: §6$rate",
            10 => "§bElo§f: §6{$data->getElo()}",
            11 => "§e",
            12 => "§bIn-Queue §6$queue",
            13 => "§7---------------"
        ];
        Loader::getScoreboardsUtils()->new($player, "ObjectiveName", Loader::getScoreboardTitle());
        foreach ($lines as $line => $content) {
            Loader::getScoreboardsUtils()->setLine($player, $line, $content);
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
            2 => "§bOnline§f: §6$on",
            3 => "§bPing§f: §6$ping",
            4 => "§bTPS§f: §a{$server->getTicksPerSecond()} ({$server->getTickUsage()})",
            5 => "§7---------------"
        ];
        Loader::getScoreboardsUtils()->new($player, "ObjectiveName", Loader::getScoreboardTitle());
        foreach ($lines as $line => $content) {
            Loader::getScoreboardsUtils()->setLine($player, $line, $content);
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
            2 => "§bYour§f: §a$boxingp",
            3 => "§bOpponent§f: §c$opponentboxingp",
            4 => "§7---------------"
        ];
        Loader::getScoreboardsUtils()->new($player, "ObjectiveName", Loader::getScoreboardTitle());
        foreach ($lines as $line => $content) {
            Loader::getScoreboardsUtils()->setLine($player, $line, $content);
        }
    }
}