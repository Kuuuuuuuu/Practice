<?php

declare(strict_types=1);

namespace Nayuki\Arena;

use Nayuki\Game\Kits\KitRegistry;
use Nayuki\PracticeCore;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;

class ArenaManager
{
    /**
     * @param Player $player
     * @return void
     */
    public function onJoinNodebuff(Player $player): void
    {
        if (PracticeCore::getArenaFactory()->getNodebuffArena() == null) {
            $player->sendMessage(PracticeCore::getPrefixCore() . '§cArena is not set!');
        } else {
            $world = Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getNodebuffArena());
            if ($world instanceof World) {
                $player->getInventory()->clearAll();
                $player->setHealth($player->getMaxHealth());
                $player->getArmorInventory()->clearAll();
                $player->getEffects()->clear();
                $player->teleport($world->getSafeSpawn());
                $this->getKitNodebuff($player);
                PracticeCore::getScoreboardManager()->setArenaScoreboard($player);
            }
        }
    }

    /**
     * @param Player $player
     * @return void
     */
    public function getKitNodebuff(Player $player): void
    {
        $kit = KitRegistry::fromString('NoDebuff');
        $kit->setEffect($player);
        $player->getArmorInventory()->setContents($kit->getArmorItems());
        $player->getInventory()->setContents($kit->getInventoryItems());
    }
}
