<?php /** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Kohaku\Core\Task;

use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ArenaUtils;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class ScoreboardTask extends Task
{

    private int $titleIndex = 0;
    private Player $player;
    private array $titles = ["§bH", "§bHo", "§bHor", "§bHori", "§bHoriz", "§bHorizo", "§bHorizon", "§k§f&&&&&&&&&&"];

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function onRun(): void
    {
        if ($this->player->isOnline()) {
            $this->titleIndex++;
            if ($this->player->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                $this->sb($this->player);
            } else if ($this->player->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld() and $this->player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
                $this->sb2($this->player);
            } else if ($this->player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
                $this->Parkour($this->player);
            }
        }
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
            10 => "§7---------------"
        ];
        if (!isset($this->titles[$this->titleIndex])) $this->titleIndex = 0;
        Loader::$score->new($player, "ObjectiveName", $this->titles[$this->titleIndex]);
        foreach ($lines as $line => $content)
            Loader::$score->setLine($player, $line, $content);
    }

    public function sb2(Player $player): void
    {
        $ping = $player->getNetworkSession()->getPing();
        $server = Server::getInstance();
        $skill = Loader::getInstance()->SkillCooldown[$player->getName() ?? null] ?? 0;
        $on = count(Server::getInstance()->getOnlinePlayers());
        $lines = [
            1 => "§7---------------§0",
            2 => "§bOnline§f: §6$on",
            3 => "§bPing§f: §6$ping",
            4 => "§bTPS§f: §a{$server->getTicksPerSecond()} ({$server->getTickUsage()})",
            5 => "§7---------------"
        ];
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena())) {
            $line[6] = "§bSkillCD§f: §6$skill";
        }
        if (!isset($this->titles[$this->titleIndex])) $this->titleIndex = 0;
        Loader::$score->new($player, "ObjectiveName", $this->titles[$this->titleIndex]);
        foreach ($lines as $line => $content)
            Loader::$score->setLine($player, $line, $content);
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
        if (!isset($this->titles[$this->titleIndex])) $this->titleIndex = 0;
        Loader::$score->new($player, "ObjectiveName", $this->titles[$this->titleIndex]);
        foreach ($lines as $line => $content)
            Loader::$score->setLine($player, $line, $content);
    }
}
