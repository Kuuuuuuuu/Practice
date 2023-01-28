<?php

declare(strict_types=1);

namespace Nayuki\Arena;

use Nayuki\Game\Kits\KitRegistry;
use Nayuki\PracticeCore;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;

final class ArenaManager
{
    /**
     * @param Player $player
     * @param string $modes
     * @return void
     */
    public function joinArenas(Player $player, string $modes): void
    {
        $mode = PracticeCore::getArenaFactory()->getArenas($modes);
        if ($mode == null) {
            $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'Arena not available');
        } else {
            $world = Server::getInstance()->getWorldManager()->getWorldByName($mode);
            if ($world instanceof World) {
                $player->getInventory()->clearAll();
                $player->setHealth($player->getMaxHealth());
                $player->getArmorInventory()->clearAll();
                $player->getEffects()->clear();
                $this->getKits($player, $modes);
                PracticeCore::getPracticeUtils()->playSound('jump.slime', $player);
                $player->teleport($world->getSpawnLocation());
            }
        }
    }

    /**
     * @param Player $player
     * @param string $mode
     * @return void
     */
    public function getKits(Player $player, string $mode): void
    {
        $kit = KitRegistry::fromString($mode);
        $kit->setEffect($player);
        $player->getArmorInventory()->setContents($kit->getArmorItems());
        $player->getInventory()->setContents($kit->getInventoryItems());
    }
}
