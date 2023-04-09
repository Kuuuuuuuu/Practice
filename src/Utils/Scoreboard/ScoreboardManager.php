<?php

namespace Nayuki\Utils\Scoreboard;

use Nayuki\Misc\Time;
use Nayuki\PracticeCore;
use pocketmine\player\Player;
use pocketmine\Server;

use function count;
use function round;

final class ScoreboardManager
{
    /**
     * @param Player $player
     * @return void
     */
    public function setLobbyScoreboard(Player $player): void
    {
        $session = PracticeCore::getSessionManager()->getSession($player);
        $ping = $player->getNetworkSession()->getPing();
        $kills = $session->getKills();
        $rate = round($session->getKdr(), 2);
        $deaths = $session->getDeaths();
        $queue = $this->getQueuePlayer();
        $duel = $this->getDuelPlayer();
        $on = count(PracticeCore::getSessionManager()->getSessions());
        $lines = [
            1 => '§7---------------§7',
            2 => " §bOnline§f: §a$on",
            3 => " §bPing§f: §a$ping",
            4 => ' §a',
            5 => " §bK§f: §a$kills §f| §bD§f: §a$deaths",
            6 => " §bKDR§f: §a$rate",
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

    /**
     * @return int
     */
    private function getQueuePlayer(): int
    {
        return array_reduce(PracticeCore::getSessionManager()->getSessions(), function ($count, $session) {
            return $count + ($session->isQueueing ? 1 : 0);
        }, 0);
    }

    /**
     * @return int
     */
    private function getDuelPlayer(): int
    {
        return array_reduce(PracticeCore::getSessionManager()->getSessions(), function ($count, $session) {
            return $count + ($session->isDueling ? 1 : 0);
        }, 0);
    }

    /**
     * @param Player $player
     * @param bool $duel
     * @return void
     */
    public function setArenaScoreboard(Player $player, bool $duel): void
    {
        $OpponentPing = 0;
        $duelTime = 0;
        $session = PracticeCore::getSessionManager()->getSession($player);
        $duelClass = $session->DuelClass;
        $ping = $player->getNetworkSession()->getPing();
        $CombatSecond = $session->CombatTime;
        $OpponentName = $session->getOpponent();
        if ($OpponentName !== null) {
            $OpponentPlayer = PracticeCore::getSessionManager()->getPlayerInSessionByPrefix($OpponentName);
            if ($OpponentPlayer instanceof Player) {
                $OpponentPing = $OpponentPlayer->getNetworkSession()->getPing();
            }
        }
        $lines = [
            1 => '§7---------------§0',
            4 => ' §d',
            6 => " §bYour §fPing: §a$ping" . '§fms',
            7 => " §bTheir §fPing: §c$OpponentPing" . '§fms',
            8 => '§7---------------'
        ];
        if ($duel) {
            if ($duelClass !== null) {
                $duelTime = Time::calculateTime($duelClass->getSeconds());
            }
            $lines[3] = " §bDuration: §a$duelTime";
        } else {
            $lines[2] = " §bCombat§f: §a$CombatSecond";
            $lines[3] = ' §bKillStreak§f: §a' . $session->getStreak();
        }
        PracticeCore::getScoreboardUtils()->new($player, 'ObjectiveName', PracticeCore::getScoreboardTitle());
        foreach ($lines as $line => $content) {
            PracticeCore::getScoreboardUtils()->setLine($player, $line, $content);
        }
    }

    /**
     * @param Player $player
     * @return void
     */
    public function Boxing(Player $player): void
    {
        $session = PracticeCore::getSessionManager()->getSession($player);
        $duelClass = $session->DuelClass;
        $duelTime = 0;
        $opponent = $session->getOpponent();
        $ping = $player->getNetworkSession()->getPing();
        $PlayerPoint = $session->BoxingPoint;
        $OpponentPoint = 0;
        $PingOpponent = 0;
        if ($opponent !== null) {
            $OpponentPlayer = Server::getInstance()->getPlayerExact($opponent);
            $OpponentPoint = $OpponentPlayer ? PracticeCore::getSessionManager()->getSession($OpponentPlayer)->BoxingPoint : 0;
            $PingOpponent = $OpponentPlayer ? $OpponentPlayer->getNetworkSession()->getPing() : 0;
        }
        if ($duelClass !== null) {
            $duelTime = Time::calculateTime($duelClass->getSeconds());
        }
        $diff = abs($PlayerPoint - $OpponentPoint);
        $check = $PlayerPoint >= $OpponentPoint;
        $lines = [
            1 => '§7---------------§0',
            2 => ' Hits: ' . ($check ? "§a(+$diff)" : "§c(-$diff)"),
            3 => "   §bYour§f: §a$PlayerPoint",
            4 => "   §bThem§f: §c$OpponentPoint",
            5 => ' §c',
            6 => " §bDuration: §a$duelTime",
            7 => ' §d',
            8 => " §bYour §fPing: §a$ping" . '§fms',
            9 => " §bTheir §fPing: §c$PingOpponent" . '§fms',
            10 => '§7---------------'
        ];
        PracticeCore::getScoreboardUtils()->new($player, 'ObjectiveName', PracticeCore::getScoreboardTitle());
        foreach ($lines as $line => $content) {
            PracticeCore::getScoreboardUtils()->setLine($player, $line, $content);
        }
    }
}
