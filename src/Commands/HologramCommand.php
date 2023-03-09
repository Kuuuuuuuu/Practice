<?php

declare(strict_types=1);

namespace Nayuki\Commands;

use Nayuki\Entities\Hologram;
use Nayuki\PracticeCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

use function in_array;

class HologramCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'hologram',
            'Hologram Command',
            '/hologram help'
        );
        $this->setPermission('hologram.command');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($this->testPermission($sender)) {
            if ($sender instanceof Player) {
                if (isset($args[0])) {
                    switch (strtolower($args[0])) {
                        case 'spawn':
                        case 'new':
                        case 'add':
                            if (!isset($args[1])) {
                                $sender->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'Usage: /hologram spawn <type>');
                                return;
                            }
                            if (in_array($args[1], ['kills', 'deaths'], true)) {
                                $this->spawn($sender, $args[1]);
                                return;
                            }
                            $sender->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'Usage: /hologram spawn <kills|deaths>');
                            break;
                        case 'removeall':
                            foreach ($sender->getWorld()->getEntities() as $entity) {
                                if ($entity instanceof Hologram) {
                                    $entity->flagForDespawn();
                                }
                            }
                            break;
                        case 'help':
                            $sender->sendMessage('/hologram spawn <type> | /hologram removeall');
                            break;
                        default:
                            $sender->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . "Subcommand '$args[0]' not found! Try '/hologram help' for help.");
                            break;
                    }
                }
            } else {
                $sender->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'You can only use this command in-Game!');
            }
        }
    }

    /**
     * @param Player $player
     * @param string $type
     * @return int|null
     */
    public function spawn(Player $player, string $type): ?int
    {
        $nbt = PracticeCore::getUtils()->createBaseNBT($player->getPosition(), null, $player->getLocation()->getYaw(), $player->getLocation()->getPitch());
        $pos = $player->getLocation();
        $nbt->setString('type', $type);
        $entity = $this->createEntity($pos, $nbt);
        if ($entity instanceof Entity) {
            $entity->setNameTagAlwaysVisible();
            $entity->spawnToAll();
            $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::GREEN . 'Hologram' . ' created successfully! ID: ' . $entity->getId());
            return $entity->getId();
        }
        return null;
    }

    /**
     * @param Location $location
     * @param CompoundTag $nbt
     * @return Entity|null
     */
    public function createEntity(Location $location, CompoundTag $nbt): ?Entity
    {
        return new Hologram($location, $nbt);
    }
}
