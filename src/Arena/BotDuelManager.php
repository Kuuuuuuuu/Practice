<?php

namespace Kuu\Arena;

use Exception;
use Kuu\PracticeCaches;
use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use Kuu\Utils\Kits\KitManager;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\generator\Flat;
use pocketmine\world\WorldCreationOptions;

class BotDuelManager
{
    use SingletonTrait;

    /**
     * @throws Exception
     */
    public function createMatch(PracticePlayer $player, KitManager $kit, string $mode): void
    {
        $worldName = 'Bot-' . $player->getName() . ' - ' . PracticeCore::getPracticeUtils()->generateUUID();
        $world = new WorldCreationOptions();
        $world->setGeneratorClass(Flat::class);
        Server::getInstance()->getWorldManager()->generateWorld($worldName, $world);
        $player->getInventory()->clearAll();
        $player->setDueling(true);
        $this->addMatch($worldName, new BotDuelFactory($worldName, $player, $kit, $mode));
    }

    public function addMatch(string $name, BotDuelFactory $task): void
    {
        PracticeCore::getCaches()->DuelMatch[$name] = $task;
    }

    public function stopMatch(string $name): void
    {
        if (Server::getInstance()->getWorldManager()->isWorldLoaded($name)) {
            Server::getInstance()->getWorldManager()->unloadWorld(Server::getInstance()->getWorldManager()->getWorldByName($name));
        }
        PracticeCore::getPracticeUtils()->deleteDir(PracticeCore::getInstance()->getServer()->getDataPath() . "worlds/$name");
        PracticeCore::getCoreTask()?->removeDuelTask($name);
        if ($this->isMatch($name)) {
            unset(PracticeCore::getCaches()->DuelMatch[$name]);
        }
    }

    public function isMatch($name): bool
    {
        return isset(PracticeCore::getCaches()->DuelMatch[$name]);
    }
}