<?php

declare(strict_types=1);

namespace Kuu;

use Kuu\Utils\ChunkManager;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;

class PracticeUtils
{
    /**
     * @param Player $player
     * @return void
     */
    public function ChunkLoader(Player $player): void
    {
        $pos = $player->getPosition();
        PracticeCore::getInstance()->getPracticeUtils()->onChunkGenerated($pos->getWorld(), (int)$player->getPosition()->getX() >> 4, (int)$player->getPosition()->getZ() >> 4, function () use ($player, $pos) {
            $player->teleport($pos);
        });
    }

    /**
     * @param World $world
     * @param int $x
     * @param int $z
     * @param callable $callable
     * @return void
     */
    public static function onChunkGenerated(World $world, int $x, int $z, callable $callable): void
    {
        if ($world->isChunkPopulated($x, $z)) {
            ($callable)();
        } else {
            $world->registerChunkLoader(new ChunkManager($world, $x, $z, $callable), $x, $z);
        }
    }

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
        if ($newStreak / 5 === 1) {
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
            $NameTag = '§a ' . $player->getDisplayName() . '§r§a > §r' . $message;
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
        $player->getOffHandInventory()->clearAll();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->getInventory()->setItem(4, $item);
    }
}
