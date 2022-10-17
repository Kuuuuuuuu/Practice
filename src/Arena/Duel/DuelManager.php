<?php

namespace Kuu\Arena\Duel;

use Exception;
use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use Kuu\Utils\Generator\DuelGenerator;
use Kuu\Utils\Generator\SumoGenerator;
use Kuu\Utils\Kits\KitManager;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\WorldCreationOptions;

class DuelManager extends DuelManagerBase
{
    /**
     * @throws Exception
     */
    public function createMatch(PracticePlayer $player1, PracticePlayer $player2, KitManager $kit): void
    {
        $worldName = 'Duel-' . $player1->getName() . '-' . $player2->getName() . ' - ' . PracticeCore::getPracticeUtils()->generateUUID();
        $world = new WorldCreationOptions();
        if ($kit->getName() !== 'Sumo') {
            $world->setGeneratorClass(DuelGenerator::class);
        } else {
            $world->setGeneratorClass(SumoGenerator::class);
        }
        $world->setSeed(0);
        $world->setSpawnPosition(new Vector3(0, 100, 0));
        Server::getInstance()->getWorldManager()->generateWorld($worldName, $world);
        foreach ([$player1, $player2] as $player) {
            $player->getInventory()->clearAll();
            $player->setDueling(true);
        }
        $this->addMatch($worldName, new DuelFactory($worldName, $player1, $player2, $kit));
    }

    /**
     * @throws Exception
     */
    public function createBotMatch(PracticePlayer $player, KitManager $kit, int $mode): void
    {
        $worldName = 'Bot-' . $player->getName() . ' - ' . PracticeCore::getPracticeUtils()->generateUUID();
        $world = new WorldCreationOptions();
        $world->setGeneratorClass(DuelGenerator::class);
        $world->setSeed(0);
        $world->setSpawnPosition(new Vector3(0, 100, 0));
        Server::getInstance()->getWorldManager()->generateWorld($worldName, $world);
        $player->getInventory()->clearAll();
        $player->setDueling(true);
        $this->addMatch($worldName, new BotDuelFactory($worldName, $player, $kit, $mode));
    }
}