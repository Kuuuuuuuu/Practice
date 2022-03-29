<?php

namespace Kohaku\Core\Utils;

use JetBrains\PhpStorm\Pure;
use Kohaku\Core\Loader;
use pocketmine\player\Player;
use pocketmine\Server;

class ScoreboardUtils
{

    #[Pure] public static function getInstance(): ScoreboardUtils
    {
        return new ScoreboardUtils();
    }

    public function sb(Player $player): void
    {
        $ping = $player->getNetworkSession()->getPing();
        $server = Server::getInstance();
        $data = ArenaUtils::getInstance()->getData($player->getName());
        $kills = $data->getKills();
        $rate = round($data->getKdr(), 2);
        $deaths = $data->getDeaths();
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
            11 => "§7---------------"
        ];
        Loader::$score->new($player, "ObjectiveName", "§bHorizon");
        foreach ($lines as $line => $content) {
            Loader::$score->setLine($player, $line, $content);
        }
    }

    public function sb2(Player $player): void
    {
        $ping = $player->getNetworkSession()->getPing();
        $server = Server::getInstance();
        $skill = floor(Loader::getInstance()->SkillCooldown[$player->getName() ?? null] ?? 0);
        $on = count(Server::getInstance()->getOnlinePlayers());
        $lines = [
            1 => "§7---------------§0",
            2 => "§bOnline§f: §6$on",
            3 => "§bPing§f: §6$ping",
            4 => "§bTPS§f: §a{$server->getTicksPerSecond()} ({$server->getTickUsage()})",
            6 => "§7---------------"
        ];
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena())) {
            $lines[5] = "§bSkillCD§f: §6$skill";
        }
        Loader::$score->new($player, "ObjectiveName", "§bHorizon");
        foreach ($lines as $line => $content) {
            Loader::$score->setLine($player, $line, $content);
        }
    }

    public function Parkour(Player $player): void
    {
        $ping = $player->getNetworkSession()->getPing();
        $jump = Loader::getInstance()->JumpCount[$player->getName() ?? null] ?? 0;
        $on = count(Server::getInstance()->getOnlinePlayers());
        $lines = [
            1 => "§7---------------§0",
            2 => "§bOnline§f: §6$on",
            3 => "§bPing§f: §6$ping",
            4 => "§bJump Count§f: §6$jump",
            5 => "§7---------------"
        ];
        Loader::$score->new($player, "ObjectiveName", "§bHorizon");
        foreach ($lines as $line => $content) {
            Loader::$score->setLine($player, $line, $content);
        }
    }
}