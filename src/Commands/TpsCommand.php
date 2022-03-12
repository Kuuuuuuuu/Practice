<?php

declare(strict_types=1);

namespace Kohaku\Core\Commands;

use Kohaku\Core\Loader;
use pocketmine\command\{Command, CommandSender};
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class TpsCommand extends Command
{

    public function __construct()
    {
        parent::__construct(
            "tps",
            "Check TPS",
            "/tps",
            ["tps"]
        );
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        $tpsColor = TextFormat::GREEN;
        $server = Server::getInstance();
        if ($server->getTicksPerSecond() < 17) {
            $tpsColor = TextFormat::YELLOW;
        }
        $sender->sendMessage(Loader::getInstance()->getPrefixCore() . "§eServer Performance");
        $sender->sendMessage("\n");
        $sender->sendMessage("§l§a» §r§fCurrent TPS: $tpsColor{$server->getTicksPerSecond()} ({$server->getTickUsage()}%)");
        $sender->sendMessage("§l§a» §r§fAverage TPS: $tpsColor{$server->getTicksPerSecondAverage()} ({$server->getTickUsageAverage()}%)");
        return true;
    }
}