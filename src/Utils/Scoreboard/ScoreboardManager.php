<?php

namespace Nayuki\Utils\Scoreboard;

use Nayuki\Game\Kits\Kit;
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
        $session = PracticeCore::getPlayerSession()::getSession($player);
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
     * @param Player $player1
     * @param Player $player2
     * @param Kit $kit
     * @param int $sec
     * @return void
     */
    public function setDuelScoreboard(Player $player1, Player $player2, Kit $kit, int $sec): void
    {
        $player1Session = PracticeCore::getPlayerSession()::getSession($player1);
        $player2Session = PracticeCore::getPlayerSession()::getSession($player2);
        $player1ping = $player1->getNetworkSession()->getPing();
        $player2ping = $player2->getNetworkSession()->getPing();
        if ($kit->getName() === 'Boxing') {
            $player1BoxingPoint = $player1Session->BoxingPoint;
            $player2BoxingPoint = $player2Session->BoxingPoint;
            $lines = [
                1 => '§7---------------§0',
                2 => " §f§l{$player1->getName()}",
                3 => "   §fPoint: §b$player1BoxingPoint",
                4 => "   §fPing: §b$player1ping" . '§fms',
                5 => ' §d',
                6 => ' §bSeconds§f: §a' . $sec,
                7 => ' §a',
                8 => " §f§l{$player2->getName()}",
                9 => "   §fPoint: §b$player2BoxingPoint",
                10 => "  §fPing: §b$player2ping" . '§fms',
                11 => '§7---------------'
            ];
        } else {
            $lines = [
                1 => '§7---------------§0',
                2 => " §f§l{$player1->getName()}",
                3 => "   §fPing: §b$player1ping" . '§fms',
                4 => ' §d',
                5 => ' §bSeconds§f: §a' . $sec,
                6 => ' §a',
                7 => " §f§l{$player2->getName()}",
                8 => "   §fPing: §b$player2ping" . '§fms',
                9 => '§7---------------'
            ];
        }
        foreach ([$player1, $player2] as $player) {
            PracticeCore::getScoreboardUtils()->new($player, 'ObjectiveName', PracticeCore::getScoreboardTitle());
            foreach ($lines as $line => $content) {
                PracticeCore::getScoreboardUtils()->setLine($player, $line, $content);
            }
        }
    }
}
