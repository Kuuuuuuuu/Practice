<?php

namespace Kuu\Commands;

use JsonException;
use Kuu\Entity\DeathLeaderboard;
use Kuu\Entity\KillLeaderboard;
use Kuu\PracticeCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as Color;

class HologramCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'hologram',
            'Place Hologram in the world'
        );
    }

    /**
     * @throws JsonException
     */
    public function execute(CommandSender $sender, string $commandLabel, ?array $args): void
    {
        if ($sender instanceof Player) {
            if (!isset($args[0])) {
                $sender->sendMessage(Color::BOLD . Color::WHITE . '>> ' . Color::RESET . Color::RED . 'use /hologram help');
            } elseif ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                switch ($args[0]) {
                    case 'help':
                        $sender->sendMessage(Color::GREEN . '/' . $commandLabel . Color::AQUA . ' setkillleader - setdeathleader - removeleader');
                        break;
                    case 'setkillleader':
                        $npc = new KillLeaderboard($sender->getLocation(), $sender->getSkin());
                        $npc->spawnToAll();
                        break;
                    case 'setdeathleader':
                        $npc = new DeathLeaderboard($sender->getLocation(), $sender->getSkin());
                        $npc->spawnToAll();
                        break;
                    case 'removeleader':
                        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
                            foreach ($world->getEntities() as $entity) {
                                if ($entity instanceof KillLeaderboard || $entity instanceof DeathLeaderboard) {
                                    $entity->close();
                                }
                            }
                        }
                        $sender->sendMessage(PracticeCore::getPrefixCore() . 'KillLeaderboard removed!');
                        break;
                }
            } else {
                $sender->sendMessage(PracticeCore::getPrefixCore() . '§cYou cannot execute this command.');
            }
        } else {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cYou can only use this command in-game!');
        }
    }
}