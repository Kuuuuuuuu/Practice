<?php

declare(strict_types=1);

namespace Kuu\Arena;

use Exception;
use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use Kuu\Utils\Kits\KitManager;
use pocketmine\Server;

class DuelManager extends DuelManagerBase
{
    /**
     * @throws Exception
     */
    public function createMatch(PracticePlayer $player1, PracticePlayer $player2, KitManager $kit): void
    {
        $worldName = 'Duel-' . $player1->getName() . '-' . $player2->getName() . ' - ' . PracticeCore::getPracticeUtils()->generateUUID();
        $world = self::Load($kit, 'Duel');
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
    public function createBotMatch(PracticePlayer $player, KitManager $kit, string $mode): void
    {
        $worldName = 'Bot-' . $player->getName() . ' - ' . PracticeCore::getPracticeUtils()->generateUUID();
        $world = self::Load($kit, 'Bot');
        Server::getInstance()->getWorldManager()->generateWorld($worldName, $world);
        $player->getInventory()->clearAll();
        $player->setDueling(true);
        $this->addMatch($worldName, new BotDuelFactory($worldName, $player, $kit, $mode));
    }
}