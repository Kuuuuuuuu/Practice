<?php

namespace Kuu\Utils;

use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use pocketmine\player\Player;
use pocketmine\Server;

class ScoreboardManager
{

    public function sb(Player $player): void
    {
        $ping = $player->getNetworkSession()->getPing();
        $data = PracticeCore::getInstance()->getPracticeUtils()->getData($player->getName());
        $kills = $data->getKills();
        $rate = round($data->getKdr(), 2);
        $deaths = $data->getDeaths();
        $queue = $this->getQueuePlayer();
        $duel = $this->getDuelPlayer();
        $on = count(Server::getInstance()->getOnlinePlayers());
        $lines = [
            1 => '§7---------------§7',
            2 => "§dOnline§f: §a$on",
            3 => "§dPing§f: §a$ping",
            4 => '§a',
            5 => "§dK§f: §a$kills §dD§f: §a$deaths",
            6 => "§dKDR§f: §a$rate",
            7 => '§e',
            8 => "§dIn-Queue §a$queue",
            9 => "§dIn-Duel §a$duel",
            10 => '§7---------------'
        ];
        PracticeCore::getScoreboardUtils()->new($player, 'ObjectiveName', PracticeCore::getScoreboardTitle());
        foreach ($lines as $line => $content) {
            PracticeCore::getScoreboardUtils()->setLine($player, $line, $content);
        }
    }

    private function getQueuePlayer(): int
    {
        $queue = 0;
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (($player instanceof PracticePlayer) && $player->isInQueue()) {
                $queue++;
            }
        }
        return $queue ?? 0;
    }

    private function getDuelPlayer(): int
    {
        $duel = 0;
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (($player instanceof PracticePlayer) && $player->isDueling()) {
                $duel++;
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
            1 => '§7---------------§0',
            2 => "§dOnline§f: §a$on",
            3 => "§dPing§f: §a$ping",
            4 => "§dTPS§f: §a{$server->getTicksPerSecond()} ({$server->getTickUsage()})",
            5 => '§7---------------'
        ];
        PracticeCore::getScoreboardUtils()->new($player, 'ObjectiveName', PracticeCore::getScoreboardTitle());
        foreach ($lines as $line => $content) {
            PracticeCore::getScoreboardUtils()->setLine($player, $line, $content);
        }
    }

    public function Boxing(Player $player): void
    {
        if ($player instanceof PracticePlayer) {
            $boxingp = PracticeCore::getCaches()->BoxingPoint[$player->getName()] ?? 0;
            $opponent = $player->getOpponent();
            if ($opponent !== null) {
                $oppopl = Server::getInstance()->getPlayerByPrefix($opponent);
                /** @var PracticePlayer $oppopl */
                $opponentboxingp = PracticeCore::getCaches()->BoxingPoint[$oppopl?->getName()] ?? 0;
            } else {
                $opponentboxingp = 0;
            }
            $lines = [
                1 => '§7---------------§0',
                2 => "§dYour§f: §a$boxingp",
                3 => "§dOpponent§f: §c$opponentboxingp",
                4 => '§7---------------'
            ];
            PracticeCore::getScoreboardUtils()->new($player, 'ObjectiveName', PracticeCore::getScoreboardTitle());
            foreach ($lines as $line => $content) {
                PracticeCore::getScoreboardUtils()->setLine($player, $line, $content);
            }
        }
    }
}