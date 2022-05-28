<?php

namespace Kuu\Arena;

use Exception;
use JetBrains\PhpStorm\Pure;
use Kuu\Loader;
use Kuu\NeptunePlayer;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\generator\Flat;
use pocketmine\world\WorldCreationOptions;

class BotDuelManager
{
    use SingletonTrait;

    private array $matches = [];

    /**
     * @throws Exception
     */
    public function createMatch(NeptunePlayer $player): void
    {
        $worldName = 'Bot-' . $player->getName() . ' - ' . Loader::getArenaUtils()->generateUUID();
        $world = new WorldCreationOptions();
        $world->setGeneratorClass(Flat::class);
        Server::getInstance()->getWorldManager()->generateWorld($worldName, $world);
        $player->getInventory()->clearAll();
        $player->setDueling(true);
        $this->addMatch($worldName, new BotDuelFactory($worldName, $player));
    }

    #[Pure] public function addMatch(string $name, BotDuelFactory $task): void
    {
        $this->getMatches()[$name] = $task;
    }

    public function getMatches(): array
    {
        return $this->matches;
    }

    public function stopMatch(string $name): void
    {
        if (Server::getInstance()->getWorldManager()->isWorldLoaded($name)) {
            Server::getInstance()->getWorldManager()->unloadWorld(Server::getInstance()->getWorldManager()->getWorldByName($name));
        }
        Loader::getArenaUtils()->deleteDir(Loader::getInstance()->getServer()->getDataPath() . "worlds/$name");
        Loader::getCoreTask()?->removeDuelTask($name);
        if ($this->isMatch($name)) {
            unset($this->matches[$name]);
        }
    }

    #[Pure] public function isMatch($name): bool
    {
        return isset($this->getMatches()[$name]);
    }
}