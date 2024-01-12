<?php

namespace Nayuki\Utils\Scoreboard;

use Nayuki\Game\Duel\Duel;
use Nayuki\Game\Duel\DuelBot;
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
        $scoreboardUtils = PracticeCore::getScoreboardUtils();
        $sessionManager = PracticeCore::getSessionManager();
        $ping = $player->getNetworkSession()->getPing();
        $session = $sessionManager->getSession($player);

        $onlinePlayers = count($sessionManager->getSessions());
        $kills = $session->getKills();
        $deaths = $session->getDeaths();
        $kdr = round($session->getKdr(), 2);
        $queue = $this->getQueuePlayer();
        $duel = $this->getDuelPlayer();

        $lines = [
            1 => '§7---------------§7',
            2 => " §dOnline§f: §a$onlinePlayers",
            3 => " §dPing§f: §a$ping",
            4 => ' §a',
            5 => " §dK§f: §a$kills §f| §dD§f: §a$deaths",
            6 => " §dKDR§f: §a$kdr",
            7 => ' §e',
            8 => " §dIn-Queue§f: §a$queue",
            9 => " §dIn-Duel§f: §a$duel",
            10 => '§7---------------'
        ];

        $scoreboardUtils->new($player, PracticeCore::getScoreboardTitle());

        array_walk($lines, static function ($content, $line) use ($player, $scoreboardUtils) {
            $scoreboardUtils->setLine($player, $line, $content);
        });
    }


    /**
     * @return int
     */
    private function getQueuePlayer(): int
    {
        $sessions = PracticeCore::getSessionManager()->getSessions();
        $queuePlayers = array_filter($sessions, static function ($session) {
            return $session->isQueueing;
        });

        return count($queuePlayers);
    }

    private function getDuelPlayer(): int
    {
        $sessions = PracticeCore::getSessionManager()->getSessions();
        $duelPlayers = array_filter($sessions, static function ($session) {
            return $session->isDueling;
        });

        return count($duelPlayers);
    }


    /**
     * @param Player $player
     * @param bool $duel
     * @return void
     */
    public function setArenaScoreboard(Player $player, bool $duel): void
    {
        $scoreboardUtils = PracticeCore::getScoreboardUtils();
        $sessionManager = PracticeCore::getSessionManager();

        $session = $sessionManager->getSession($player);
        $duelClass = $session->DuelClass;
        $ping = $player->getNetworkSession()->getPing();
        $opponentName = $session->getOpponent();
        $opponentPing = 0;

        if ($opponentName !== null) {
            $opponentPlayer = $sessionManager->getPlayerInSessionByPrefix($opponentName);

            if ($opponentPlayer instanceof Player) {
                $opponentPing = $opponentPlayer->getNetworkSession()->getPing();
            }
        }

        $lines = [
            1 => '§7---------------§0',
            4 => ' §d',
            6 => " §dYour §fPing: §a$ping" . '§fms',
            7 => " §dTheir §fPing: §c$opponentPing" . '§fms',
            8 => '§7---------------'
        ];

        if ($duel) {
            $duelTime = $duelClass !== null ? Time::calculateTime($duelClass->getSeconds()) : 0;
            $lines[3] = " §dDuration: §a$duelTime";
        } else {
            $combatSecond = $session->CombatTime;
            $lines[2] = " §dCombat§f: §a$combatSecond";
            $lines[3] = ' §dKillStreak§f: §a' . $session->getStreak();
        }

        $scoreboardUtils->new($player, PracticeCore::getScoreboardTitle());

        array_walk($lines, static function ($content, $line) use ($player, $scoreboardUtils) {
            $scoreboardUtils->setLine($player, $line, $content);
        });
    }


    /**
     * @param Player $player
     * @return void
     */
    public function Boxing(Player $player): void
    {
        $scoreboardUtils = PracticeCore::getScoreboardUtils();
        $sessionManager = PracticeCore::getSessionManager();

        $session = $sessionManager->getSession($player);
        $duelClass = $session->DuelClass ?? null;
        $duelTime = $duelClass ? Time::calculateTime($duelClass->getSeconds()) : 0;

        $opponent = $session->getOpponent();
        $ping = $player->getNetworkSession()->getPing();
        $playerPoint = $session->BoxingPoint;

        $opponentPoint = 0;
        $pingOpponent = 0;

        if ($opponent !== null) {
            $opponentPlayer = Server::getInstance()->getPlayerExact($opponent);

            if ($opponentPlayer instanceof Player) {
                $opponentSession = $sessionManager->getSession($opponentPlayer);
                $opponentPoint = $opponentSession->BoxingPoint ?? 0;
                $pingOpponent = $opponentPlayer->getNetworkSession()->getPing() ?? 0;
            }
        }

        $diff = abs($playerPoint - $opponentPoint);
        $check = $playerPoint >= $opponentPoint;

        $lines = [
            1 => '§7---------------§0',
            2 => ' Hits: ' . ($check ? "§a(+$diff)" : "§c(-$diff)"),
            3 => "   §dYour§f: §a$playerPoint",
            4 => "   §dThem§f: §c$opponentPoint",
            5 => ' §c',
            6 => " §dDuration: §a$duelTime",
            7 => ' §d',
            8 => " §dYour §fPing: §a$ping" . '§fms',
            9 => " §dTheir §fPing: §c$pingOpponent" . '§fms',
            10 => '§7---------------'
        ];

        $scoreboardUtils->new($player, PracticeCore::getScoreboardTitle());

        array_walk($lines, static function ($content, $line) use ($player, $scoreboardUtils) {
            $scoreboardUtils->setLine($player, $line, $content);
        });
    }

    /**
     * @param Player $player
     * @return void
     */
    public function setSpectatorScoreboard(Player $player): void
    {
        $scoreboardUtils = PracticeCore::getScoreboardUtils();
        $session = PracticeCore::getSessionManager()->getSession($player);
        $spectatingDuel = $session->spectatingDuel;

        if ($spectatingDuel === null) {
            return;
        }

        $lines = [
            1 => '§7---------------§0',
            2 => ' §d',
            8 => ' §d',
            10 => '§7---------------'
        ];

        if ($spectatingDuel instanceof Duel) {
            [$duelPlayer1, $duelPlayer2] = [$spectatingDuel->player1, $spectatingDuel->player2];
            [$player1Name, $player1Health, $player2Name, $player2Health] = [$duelPlayer1->getName(), $duelPlayer1->getHealth(), $duelPlayer2->getName(), $duelPlayer2->getHealth()];
            $timeLeft = Time::calculateTime($spectatingDuel->getSeconds());

            $lines[3] = " §a{$player1Name}§f(§a{$player1Health}§f)";
            $lines[4] = ' §dVS';
            $lines[5] = " §c{$player2Name}§f(§c{$player2Health}§f)";
            $lines[6] = " §dDuration: §a $timeLeft";

            if ($spectatingDuel->kit->getName() === 'Boxing') {
                $player1Point = PracticeCore::getSessionManager()->getSession($duelPlayer1)->BoxingPoint;
                $player2Point = PracticeCore::getSessionManager()->getSession($duelPlayer2)->BoxingPoint;

                $lines[3] = " §a{$player1Name}§f(§a{$player1Point}§f)";
                $lines[4] = ' §dVS';
                $lines[5] = " §c{$player2Name}§f(§c{$player2Point}§f)";
                $lines[6] = ' §dDiff: §a' . abs($player1Point - $player2Point);
            }
        } elseif ($spectatingDuel instanceof DuelBot) {
            [$playerName, $playerHealth] = [$spectatingDuel->player1->getName(), $spectatingDuel->player1->getHealth()];
            [$botName, $botHealth] = ['PracticeBot', ($spectatingDuel->player2?->getHealth() ?? 0)];
            $timeLeft = Time::calculateTime($spectatingDuel->getSeconds());

            $lines[3] = " §a{$playerName}§f(§a{$playerHealth}§f)";
            $lines[4] = ' §dVS';
            $lines[5] = " §c{$botName}§f(§c{$botHealth}§f)";
            $lines[6] = " §dDuration: §a $timeLeft";
        }

        $scoreboardUtils->new($player, PracticeCore::getScoreboardTitle());

        array_walk($lines, static function ($content, $line) use ($player, $scoreboardUtils) {
            $scoreboardUtils->setLine($player, $line, $content);
        });
    }
}
