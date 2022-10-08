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
            2 => " §bOnline§f: §a$on",
            3 => " §bPing§f: §a$ping",
            4 => ' §a',
            5 => " §bK§f: §a$kills §bD§f: §a$deaths",
            6 => " §bKDR§f: §a$rate §bElo§f: §a{$data->getElo()}",
            7 => ' §e',
            8 => " §bIn-Queue§f: §a$queue",
            9 => " §bIn-Duel§f: §a$duel",
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
                $pingoppo = $oppopl?->getNetworkSession()->getPing();
            } else {
                $pingoppo = 0;
            }
            $ping = $player->getNetworkSession()->getPing();
            $on = count(Server::getInstance()->getOnlinePlayers());
            $combat = $player->CombatTime;
            $lines = [
                1 => '§7---------------§0',
                2 => " §bOnline§f: §a$on",
                3 => " §bCombat§f: §a$combat",
                4 => ' §d',
                5 => " §bYour §fPing: §a$ping" . '§fms',
                6 => " §bTheir §fPing: §c$pingoppo" . '§fms',
                7 => '§7---------------'
            ];
            PracticeCore::getScoreboardUtils()->new($player, 'ObjectiveName', PracticeCore::getScoreboardTitle());
            foreach ($lines as $line => $content) {
                PracticeCore::getScoreboardUtils()->setLine($player, $line, $content);
            }
        }
    }

    public function Parkour(Player $player): void
    {
        if ($player instanceof PracticePlayer) {
            $ping = $player->getNetworkSession()->getPing();
            $on = count(Server::getInstance()->getOnlinePlayers());
            if (isset(PracticeCore::getCaches()->ParkourLeaderboard[$player->getName()])) {
                $data = PracticeCore::getCaches()->ParkourLeaderboard[$player->getName()];
                $bestmins = floor($data / 6000);
                $bestsecs = floor(($data / 100) % 60);
                $bestmili = $data % 100;
            } else {
                $bestmins = 0;
                $bestsecs = 0;
                $bestmili = 0;
            }
            $mins = floor($player->TimerSec / 6000);
            $secs = floor(($player->TimerSec / 100) % 60);
            $mili = $player->TimerSec % 100;
            $lines = [
                1 => '§7---------------§0',
                2 => " §bOnline§f: §a$on",
                3 => ' §c',
                4 => ' Timer: ',
                5 => '   §aNow: ' . $mins . ' : ' . $secs . ' : ' . $mili,
                6 => '   §aBest: ' . $bestmins . ' : ' . $bestsecs . ' : ' . $bestmili,
                7 => ' §d',
                8 => " §bYour §fPing: §a$ping" . '§fms',
                9 => '§7---------------'
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
            $combat = $player->CombatTime;
            $ping = $player->getNetworkSession()->getPing();
            $boxingp = $player->BoxingPoint;
            $opponent = $player->getOpponent();
            if ($opponent !== null) {
                $oppopl = Server::getInstance()->getPlayerByPrefix($opponent);
                assert($oppopl instanceof PracticePlayer);
                $opponentboxingp = $oppopl->BoxingPoint;
                $pingoppo = $oppopl->getNetworkSession()->getPing();
            } else {
                $opponentboxingp = 0;
                $pingoppo = 0;
            }
            $diff = abs($boxingp - $opponentboxingp);
            $check = $boxingp >= $opponentboxingp;
            $lines = [
                1 => '§7---------------§0',
                2 => ' Hits: ' . ($check ? "§a(+$diff)" : "§c(-$diff)"),
                3 => "   §bYour§f: §a$boxingp",
                4 => "   §bThem§f: §c$opponentboxingp",
                5 => ' §c',
                6 => " §bCombat§f: §a$combat",
                7 => ' §d',
                8 => " §bYour §fPing: §a$ping" . '§fms',
                9 => " §bTheir §fPing: §c$pingoppo" . '§fms',
                10 => '§7---------------'
            ];
            PracticeCore::getScoreboardUtils()->new($player, 'ObjectiveName', PracticeCore::getScoreboardTitle());
            foreach ($lines as $line => $content) {
                PracticeCore::getScoreboardUtils()->setLine($player, $line, $content);
            }
        }
    }
}