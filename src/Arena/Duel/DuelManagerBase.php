<?php

namespace Kuu\Arena\Duel;

use Exception;
use Kuu\PracticeCore;
use pocketmine\Server;
use pocketmine\world\World;

abstract class DuelManagerBase
{
    /**
     * @param string $name
     * @param DuelFactory|BotDuelFactory $task
     * @return void
     */
    public function addMatch(string $name, DuelFactory|BotDuelFactory $task): void
    {
        PracticeCore::getCaches()->DuelMatch[$name] = $task;
    }

    /**
     * @param string $name
     * @return void
     */
    public function stopMatch(string $name): void
    {
        try {
            $world = Server::getInstance()->getWorldManager()->getWorldByName($name);
            if ($world instanceof World) {
                if ($world->isLoaded()) {
                    Server::getInstance()->getWorldManager()->unloadWorld($world);
                }
                PracticeCore::getPracticeUtils()->deleteDir(PracticeCore::getInstance()->getServer()->getDataPath() . "worlds/$name");
                PracticeCore::getCoreTask()?->removeDuelTask($name);
                if (isset(PracticeCore::getCaches()->DuelMatch[$name])) {
                    unset(PracticeCore::getCaches()->DuelMatch[$name]);
                }
            }
        } catch (Exception $e) {
            Server::getInstance()->getLogger()->error($e->getMessage());
        }
    }
}