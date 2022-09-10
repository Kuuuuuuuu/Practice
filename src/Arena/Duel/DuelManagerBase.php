<?php

declare(strict_types=1);

namespace Kuu\Arena\Duel;

use Exception;
use Kuu\PracticeCore;
use Kuu\Utils\Generator\DuelGenerator;
use Kuu\Utils\Generator\SumoGenerator;
use Kuu\Utils\Kits\KitManager;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\WorldCreationOptions;

abstract class DuelManagerBase
{

    public static function Load(KitManager $kit, string $mode): WorldCreationOptions
    {
        $world = new WorldCreationOptions();
        if ($mode === 'Duel') {
            if ($kit->getName() !== 'Sumo') {
                $world->setGeneratorClass(DuelGenerator::class);
            } else {
                $world->setGeneratorClass(SumoGenerator::class);
            }
        } elseif ($mode === 'Bot') {
            $world->setGeneratorClass(DuelGenerator::class);
        }
        $world->setSeed(0);
        $world->setSpawnPosition(new Vector3(0, 100, 0));
        return $world;
    }

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