<?php

namespace Kuu\Utils\Scoreboard;

use Kuu\PracticeCore;
use pocketmine\player\Player;

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
        $session = PracticeCore::getPlayerSession()::getSession($player);
        $ping = $player->getNetworkSession()->getPing();
        $kills = $session->getKills();
        $rate = round($session->getKdr(), 2);
        $deaths = $session->getDeaths();
        $on = count(PracticeCore::getPracticeUtils()->getPlayerInSession());
        $lines = [
            1 => '§7---------------§7',
            2 => " §bOnline§f: §a$on",
            3 => " §bPing§f: §a$ping",
            4 => ' §a',
            5 => " §bK§f: §a$kills §f| §bD§f: §a$deaths",
            6 => " §bKDR§f: §a$rate",
            7 => '§7---------------'
        ];
        PracticeCore::getScoreboardUtils()->new($player, 'ObjectiveName', PracticeCore::getScoreboardTitle());
        foreach ($lines as $line => $content) {
            PracticeCore::getScoreboardUtils()->setLine($player, $line, $content);
        }
    }

    /**
     * @param Player $player
     * @return void
     */
    public function setArenaScoreboard(Player $player): void
    {
        $OpponentPing = 0;
        $session = PracticeCore::getPlayerSession()::getSession($player);
        $ping = $player->getNetworkSession()->getPing();
        $CombatSecond = $session->CombatTime;
        $OpponentName = $session->getOpponent();
        if ($OpponentName !== null) {
            $OpponentPlayer = PracticeCore::getPracticeUtils()->getPlayerInSessionByPrefix($OpponentName);
            if ($OpponentPlayer instanceof Player) {
                $OpponentPing = $OpponentPlayer->getNetworkSession()->getPing();
            }
        }
        $lines = [
            1 => '§7---------------§0',
            2 => " §bCombat§f: §a$CombatSecond",
            3 => ' §bKillStreak§f: §a' . $session->getStreak(),
            4 => ' §d',
            5 => " §bYour §fPing: §a$ping" . '§fms',
            6 => " §bTheir §fPing: §c$OpponentPing" . '§fms',
            7 => '§7---------------'
        ];
        PracticeCore::getScoreboardUtils()->new($player, 'ObjectiveName', PracticeCore::getScoreboardTitle());
        foreach ($lines as $line => $content) {
            PracticeCore::getScoreboardUtils()->setLine($player, $line, $content);
        }
    }

    /**
     * @param Player $player
     * @return void
     */
    public function setBoxingScoreboard(Player $player): void
    {
        $session = PracticeCore::getPlayerSession()::getSession($player);
        $ping = $player->getNetworkSession()->getPing();
        $combat = $session->CombatTime;
        $BoxingPoint = $session->BoxingPoint;
        $OpponentBoxingPoint = 0;
        $OpponentPing = 0;
        $opponent = $session->getOpponent();
        if ($opponent !== null) {
            $OpponentPlayer = PracticeCore::getPracticeUtils()->getPlayerInSessionByPrefix($opponent);
            if ($OpponentPlayer instanceof Player) {
                $OpponentSession = PracticeCore::getPlayerSession()::getSession($OpponentPlayer);
                $OpponentBoxingPoint = $OpponentSession->BoxingPoint;
                $OpponentPing = $OpponentPlayer->getNetworkSession()->getPing();
            }
        }
        $diff = abs($BoxingPoint - $OpponentBoxingPoint);
        $check = $BoxingPoint >= $OpponentBoxingPoint;
        $lines = [
            1 => '§7---------------§0',
            2 => ' Hits: ' . ($check ? "§a(+$diff)" : "§c(-$diff)"),
            3 => "   §bYour§f: §a$BoxingPoint",
            4 => "   §bThem§f: §c$OpponentBoxingPoint",
            5 => ' §c',
            6 => " §bCombat§f: §a$combat",
            7 => ' §bKillStreak§f: §a' . $session->getStreak(),
            8 => ' §d',
            9 => " §bYour §fPing: §a$ping" . '§fms',
            10 => " §bTheir §fPing: §c$OpponentPing" . '§fms',
            11 => '§7---------------'
        ];
        PracticeCore::getScoreboardUtils()->new($player, 'ObjectiveName', PracticeCore::getScoreboardTitle());
        foreach ($lines as $line => $content) {
            PracticeCore::getScoreboardUtils()->setLine($player, $line, $content);
        }
    }
}
