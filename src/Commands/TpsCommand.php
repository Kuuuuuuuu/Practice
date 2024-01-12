<?php

declare(strict_types=1);

namespace Nayuki\Commands;

use Nayuki\PracticeCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

final class TpsCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'tps',
            'Check TPS',
            '/tps',
            ['tps']
        );
        $this->setPermission('default.command');
    }

    public function execute(CommandSender $sender, string $commandLabel, ?array $args): void
    {
        $server = Server::getInstance();
        $sender->sendMessage(PracticeCore::getPrefixCore() . TextFormat::YELLOW . 'Server Performance');
        $sender->sendMessage("\n");
        $sender->sendMessage(Textformat::BOLD . TextFormat::GREEN . "Current TPS: {$server->getTicksPerSecond()} ({$server->getTickUsage()}%)");
        $sender->sendMessage(Textformat::BOLD . TextFormat::GREEN . "Average TPS: {$server->getTicksPerSecondAverage()} ({$server->getTickUsageAverage()}%)");
    }
}
