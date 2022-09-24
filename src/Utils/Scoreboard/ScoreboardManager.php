<?php

namespace Kuu\Utils\Scoreboard;

use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use pocketmine\player\Player;
use pocketmine\Server;

class ScoreboardManager
{

    public function sb(PracticePlayer $player): void
    {
        $ping = $player->getNetworkSession()->getPing();
        $data = $player->getData();
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
            6 => "§dKDR§f: §a$rate §dElo§f: §a{$data->getElo()}",
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
        if ($player instanceof PracticePlayer) {
            $opponent = $player->getOpponent();
            if ($opponent !== null) {
                $oppopl = Server::getInstance()->getPlayerByPrefix($opponent);
                $pingoppo = $oppopl->getNetworkSession()->getPing();
            } else {
                $pingoppo = 0;
            }
            $ping = $player->getNetworkSession()->getPing();
            $on = count(Server::getInstance()->getOnlinePlayers());
            $lines = [
                1 => '§7---------------§0',
                2 => "§dOnline§f: §a$on",
                3 => '§c',
                4 => "§aYour §fPing: §a$ping" . ' §fms',
                5 => "§cTheir §fPing: §c$pingoppo" . ' §fms',
                6 => '§7---------------'
            ];
            PracticeCore::getScoreboardUtils()->new($player, 'ObjectiveName', PracticeCore::getScoreboardTitle());
            foreach ($lines as $line => $content) {
                PracticeCore::getScoreboardUtils()->setLine($player, $line, $content);
            }
        }
    }

    public function Boxing(Player $player): void
    {
        if ($player instanceof PracticePlayer) {
            $ping = $player->getNetworkSession()->getPing();
            $boxingp = $player->BoxingPoint;
            $opponent = $player->getOpponent();
            if ($opponent !== null) {
                $oppopl = Server::getInstance()->getPlayerByPrefix($opponent);
                /** @var PracticePlayer $oppopl */
                $opponentboxingp = $oppopl?->BoxingPoint;
                $pingoppo = $oppopl->getNetworkSession()->getPing();
            } else {
                $opponentboxingp = 0;
                $pingoppo = 0;
            }
            $lines = [
                1 => '§7---------------§0',
                2 => "§aYour§f: §a$boxingp",
                3 => "§cTheir§f: §c$opponentboxingp",
                4 => '§c',
                5 => "§aYour §fPing: §a$ping" . ' §fms',
                6 => "§cTheir §fPing: §c$pingoppo" . ' §fms',
                7 => '§7---------------'
            ];
            PracticeCore::getScoreboardUtils()->new($player, 'ObjectiveName', PracticeCore::getScoreboardTitle());
            foreach ($lines as $line => $content) {
                PracticeCore::getScoreboardUtils()->setLine($player, $line, $content);
            }
        }
    }
}