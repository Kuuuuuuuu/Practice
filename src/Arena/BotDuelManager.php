<?php

namespace Kohaku\Arena;

use JetBrains\PhpStorm\Pure;
use Kohaku\Entity\FistBot;
use Kohaku\Loader;
use Kohaku\NeptunePlayer;
use Kohaku\Utils\Kits\KitManager;
use pocketmine\entity\Location;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\generator\Flat;
use pocketmine\world\WorldCreationOptions;

class BotDuelManager
{
    use SingletonTrait;

    private array $matches = [];

    public function createMatch(NeptunePlayer $player, KitManager $kit): void
    {
        $worldName = 'Bot-' . $player->getName();
        $world = new WorldCreationOptions();
        $world->setGeneratorClass(Flat::class);
        Server::getInstance()->getWorldManager()->generateWorld($worldName, $world);
        $player->getInventory()->clearAll();
        $player->setDueling(true);
        $this->addMatch($worldName, new BotDuelFactory($worldName, $player, new FistBot(new Location(0, 4, 0, Server::getInstance()->getWorldManager()->getWorldByName($worldName), 0, 0), $player->getSkin()), $kit));
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
        Loader::getCoreTask()->removeBotDuelTask($name);
        if ($this->isMatch($name)) {
            unset($this->matches[$name]);
        }
    }

    #[Pure] public function isMatch($name): bool
    {
        return isset($this->getMatches()[$name]);
    }
}