<?php

declare(strict_types=1);

namespace Kuu;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use pocketmine\Server;
use function is_array;
use function stripos;
use function strlen;
use function strtolower;

class PracticeUtils
{
    /**
     * @param Player $player
     * @param Player $death
     * @return void
     */
    public function handleStreak(Player $player, Player $death): void
    {
        $KillSession = PracticeCore::getPlayerSession()::getSession($player);
        $DeathSession = PracticeCore::getPlayerSession()::getSession($death);
        $oldStreak = $DeathSession->getStreak();
        $newStreak = $KillSession->getStreak();
        if ($oldStreak > 10) {
            $death->sendMessage(PracticeCore::getPrefixCore() . '§r§aYour ' . $oldStreak . ' killstreak was ended by ' . $player->getName() . '!');
            $player->sendMessage(PracticeCore::getPrefixCore() . '§r§aYou have ended ' . $death->getName() . "'s " . $oldStreak . ' killstreaks!');
        }
        if ($newStreak / 5 === 0) {
            Server::getInstance()->broadcastMessage(PracticeCore::getPrefixCore() . '§r§a' . $player->getName() . ' is on a ' . $newStreak . ' killstreaks!');
        }
    }

    /**
     * @return void
     */
    public function dispose(): void
    {
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            foreach ($world->getEntities() as $entity) {
                $entity->close();
            }
        }
    }

    /**
     * @param string $name
     * @return Player|null
     */
    public function getPlayerInSessionByPrefix(string $name): ?Player
    {
        $found = null;
        $name = strtolower($name);
        $delta = PHP_INT_MAX;
        foreach ($this->getPlayerInSession() as $player) {
            if (stripos($player->getName(), $name) === 0) {
                $curDelta = strlen($player->getName()) - strlen($name);
                if ($curDelta < $delta) {
                    $found = $player;
                    $delta = $curDelta;
                }
                if ($curDelta === 0) {
                    break;
                }
            }
        }
        return $found;
    }

    /**
     * @return array<Player>
     */
    public function getPlayerInSession(): array
    {
        return PracticeCore::getCaches()->PlayerInSession;
    }

    /**
     * @param Player $player
     * @param string $message
     * @return string
     */
    public function getChatFormat(Player $player, string $message): string
    {
        $session = PracticeCore::getPlayerSession()::getSession($player);
        if ($session->getCustomTag() !== '') {
            $NameTag = '§f[' . $session->getCustomTag() . '§f] §b' . $player->getDisplayName() . '§r§a > §r' . $message;
        } else {
            $NameTag = '§a' . $player->getDisplayName() . '§r§a > §r' . $message;
        }
        return $NameTag;
    }

    /**
     * @param Player $player
     * @return void
     */
    public function setLobbyItem(Player $player): void
    {
        $item = VanillaItems::DIAMOND_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item->setCustomName('§r§bPlay');
        $item2 = VanillaItems::COMPASS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item2->setCustomName('§r§bSettings');
        $player->getOffHandInventory()->clearAll();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->getInventory()->setItem(4, $item);
        $player->getInventory()->setItem(8, $item2);
    }

    /**
     * @param Vector3 $pos
     * @param Vector3|null $motion
     * @param float $yaw
     * @param float $pitch
     * @return CompoundTag
     */
    public function createBaseNBT(Vector3 $pos, ?Vector3 $motion = null, float $yaw = 0.0, float $pitch = 0.0): CompoundTag
    {
        return CompoundTag::create()
            ->setTag('Pos', new ListTag([
                new DoubleTag($pos->x),
                new DoubleTag($pos->y),
                new DoubleTag($pos->z),
            ]))
            ->setTag('Motion', new ListTag([
                new DoubleTag($motion !== null ? $motion->x : 0.0),
                new DoubleTag($motion !== null ? $motion->y : 0.0),
                new DoubleTag($motion !== null ? $motion->z : 0.0),
            ]))
            ->setTag('Rotation', new ListTag([
                new FloatTag($yaw),
                new FloatTag($pitch),
            ]));
    }
}
