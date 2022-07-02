<?php

declare(strict_types=1);

namespace Kuu\Commands;

use Kuu\PracticeCore;
use pocketmine\command\{Command, CommandSender};
use pocketmine\Server;

class TpsCommand extends Command
{

    public function __construct()
    {
        parent::__construct(
            'tps',
            'Check TPS',
            '/tps',
            ['tps']
        );
    }

    public function execute(CommandSender $sender, string $commandLabel, ?array $args): void
    {
        $server = Server::getInstance();
        $sender->sendMessage(PracticeCore::getPrefixCore() . '§eServer Performance');
        $sender->sendMessage("\n");
        $sender->sendMessage("§l§a» §r§fCurrent TPS: {$server->getTicksPerSecond()} ({$server->getTickUsage()}%)");
        $sender->sendMessage("§l§a» §r§fAverage TPS: {$server->getTicksPerSecondAverage()} ({$server->getTickUsageAverage()}%)");
    }
}