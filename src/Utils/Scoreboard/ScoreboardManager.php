<?php

namespace Nayuki\Utils\Scoreboard;

use Nayuki\PracticeCore;
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
        $session = PracticeCore::getSessionManager()::getSession($player);
        $ping = $player->getNetworkSession()->getPing();
        $kills = $session->getKills();
        $rate = round($session->getKdr(), 2);
        $deaths = $session->getDeaths();
        $queue = $this->getQueuePlayer();
        $duel = $this->getDuelPlayer();
        $on = count(PracticeCore::getPracticeUtils()->getPlayerInSession());
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
        $queue = 0;
        foreach (PracticeCore::getPracticeUtils()->getPlayerSession() as $session) {
            if ($session->isQueueing) {
                $queue++;
            }
        }
        return $queue;
    }

    /**
     * @return int
     */
    private function getDuelPlayer(): int
    {
        $duel = 0;
        foreach (PracticeCore::getPracticeUtils()->getPlayerSession() as $session) {
            if ($session->isDueling) {
                $duel++;
            }
        }
        return $duel;
    }

    /**
     * @param Player $player
     * @param bool $duel
     * @return void
     */
    public function setArenaScoreboard(Player $player, bool $duel): void
    {
        $OpponentPing = 0;
        $session = PracticeCore::getSessionManager()::getSession($player);
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
            ($duel) ?: 2 => " §bCombat§f: §a$CombatSecond",
            ($duel) ?: 3 => ' §bKillStreak§f: §a' . $session->getStreak(),
            ($duel) ?: 4 => ' §d',
            5 => " §bYour §fPing: §a$ping" . '§fms',
            6 => " §bTheir §fPing: §c$OpponentPing" . '§fms',
            7 => '§7---------------'
        ];
        PracticeCore::getScoreboardUtils()->new($player, 'ObjectiveName', PracticeCore::getScoreboardTitle());
        foreach ($lines as $line => $content) {
            PracticeCore::getScoreboardUtils()->setLine($player, $line, $content);
        }
    }
}
