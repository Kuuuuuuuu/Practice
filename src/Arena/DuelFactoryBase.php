<?php

declare(strict_types=1);

namespace Kuu\Arena;

use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use Kuu\Task\PracticeTask;
use pocketmine\Server;
use pocketmine\world\World;
use pocketmine\world\WorldException;

abstract class DuelFactoryBase
{
    protected const INGAME = 1;
    protected const ENDED = 2;

    protected function Load(string $name, DuelFactory|BotDuelFactory $factory): World|null
    {
        $world = Server::getInstance()->getWorldManager()->getWorldByName($name);
        if ($world === null) {
            throw new WorldException('World does not exist');
        }
        if (PracticeCore::getCoreTask() instanceof PracticeTask) {
            PracticeCore::getCoreTask()?->addDuelTask($name, $factory);
        }
        return $world;
    }

    abstract protected function onEnd(?PracticePlayer $playerLeft = null): void;

    abstract protected function update(): void;
}