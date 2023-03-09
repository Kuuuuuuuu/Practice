<?php

namespace Nayuki\Game\Duel;

use Nayuki\Game\Generator\DuelGenerator;
use Nayuki\Game\Generator\SumoGenerator;
use Nayuki\Game\Kits\Kit;
use Nayuki\PracticeCore;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use Ramsey\Uuid\Uuid;

final class DuelManager
{
    /** @var Duel[] */
    private array $arenas = [];

    /**
     * @param Player $player1
     * @param Player $player2
     * @param Kit $kit
     * @return void
     */
    public function createMatch(Player $player1, Player $player2, Kit $kit): void
    {
        $worldName = 'Duel-' . $player1->getName() . '-' . $player2->getName() . ' - ' . Uuid::uuid4();
        $world = new WorldCreationOptions();
        if ($kit->getName() === 'Sumo') {
            $world->setGeneratorClass(SumoGenerator::class);
        } else {
            $world->setGeneratorClass(DuelGenerator::class);
        }
        $world->setSeed(0);
        $world->setSpawnPosition(new Vector3(0, 100, 0));
        Server::getInstance()->getWorldManager()->generateWorld($worldName, $world);
        foreach ([$player1, $player2] as $player) {
            $session = PracticeCore::getSessionManager()->getSession($player);
            $player->getInventory()->clearAll();
            $session->isDueling = true;
        }
        $this->addMatch($worldName, new Duel($worldName, $player1, $player2, $kit));
    }

    /**
     * @param string $name
     * @param Duel $task
     * @return void
     */
    public function addMatch(string $name, Duel $task): void
    {
        $this->arenas[$name] = $task;
    }

    /**
     * @param string $name
     * @return void
     */
    public function stopMatch(string $name): void
    {
        $world = Server::getInstance()->getWorldManager()->getWorldByName($name);
        if ($world instanceof World) {
            if ($world->isLoaded()) {
                Server::getInstance()->getWorldManager()->unloadWorld($world);
            }
            PracticeCore::getUtils()->deleteDir(PracticeCore::getInstance()->getServer()->getDataPath() . "worlds/$name");
            if (isset($this->arenas[$name])) {
                unset($this->arenas[$name]);
            }
        }
    }

    /**
     * @return Duel[]
     */
    public function getArenas(): array
    {
        return $this->arenas;
    }
}
