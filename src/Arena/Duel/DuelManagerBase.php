<?php

namespace Kuu\Arena\Duel;

use Exception;
use Kuu\PracticeCore;
use pocketmine\Server;

abstract class DuelManagerBase
{
    public function addMatch(string $name, DuelFactory|BotDuelFactory $task): void
    {
        PracticeCore::getCaches()->DuelMatch[$name] = $task;
    }

    public function stopMatch(string $name): void
    {
        try {
            if (Server::getInstance()->getWorldManager()->isWorldLoaded($name)) {
                Server::getInstance()->getWorldManager()->unloadWorld(Server::getInstance()->getWorldManager()->getWorldByName($name));
            }
            PracticeCore::getPracticeUtils()->deleteDir(PracticeCore::getInstance()->getServer()->getDataPath() . "worlds/$name");
            PracticeCore::getCoreTask()?->removeDuelTask($name);
            if (isset(PracticeCore::getCaches()->DuelMatch[$name])) {
                unset(PracticeCore::getCaches()->DuelMatch[$name]);
            }
        } catch (Exception $e) {
            Server::getInstance()->getLogger()->error($e->getMessage());
        }
    }
}