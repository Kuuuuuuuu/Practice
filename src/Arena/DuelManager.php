<?php

namespace Kuu\Arena;

use Exception;
use JetBrains\PhpStorm\Pure;
use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use Kuu\Utils\Generator\SumoGenerator;
use Kuu\Utils\Kits\KitManager;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\generator\Flat;
use pocketmine\world\WorldCreationOptions;

class DuelManager
{
    use SingletonTrait;

    private array $matches = [];

    /**
     * @throws Exception
     */
    public function createMatch(PracticePlayer $player1, PracticePlayer $player2, KitManager $kit): void
    {
        $worldName = 'Duel-' . $player1->getName() . '-' . $player2->getName() . ' - ' . PracticeCore::getArenaUtils()->generateUUID();
        $world = new WorldCreationOptions();
        if ($kit->getName() !== 'Sumo') {
            $world->setGeneratorClass(Flat::class);
        } else {
            $world->setGeneratorClass(SumoGenerator::class);
        }
        Server::getInstance()->getWorldManager()->generateWorld($worldName, $world);
        foreach ([$player1, $player2] as $player) {
            $player->getInventory()->clearAll();
            $player->setDueling(true);
        }
        $this->addMatch($worldName, new DuelFactory($worldName, $player1, $player2, $kit));
    }

    #[Pure] public function addMatch(string $name, DuelFactory $task): void
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
        PracticeCore::getArenaUtils()->deleteDir(PracticeCore::getInstance()->getServer()->getDataPath() . "worlds/$name");
        PracticeCore::getCoreTask()?->removeDuelTask($name);
        if ($this->isMatch($name)) {
            unset($this->matches[$name]);
        }
    }

    #[Pure] public function isMatch($name): bool
    {
        return isset($this->getMatches()[$name]);
    }
}