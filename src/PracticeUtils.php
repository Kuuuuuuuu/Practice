<?php

declare(strict_types=1);

namespace Nayuki;

use Nayuki\Entities\Hologram;
use Nayuki\Entities\JoinEntity;
use Nayuki\Players\PlayerSession;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\Server;

use function glob;
use function is_array;
use function is_dir;
use function rmdir;
use function str_ends_with;
use function stripos;
use function strlen;
use function strtolower;
use function unlink;

final class PracticeUtils
{
    /**
     * @param string $soundName
     * @param Player $player
     * @return void
     */
    public function playSound(string $soundName, Player $player): void
    {
        $location = $player->getLocation();
        $pk = new PlaySoundPacket();
        $pk->soundName = $soundName;
        $pk->volume = 1;
        $pk->pitch = 1;
        $pk->x = $location->x;
        $pk->y = $location->y;
        $pk->z = $location->z;
        $player->getNetworkSession()->sendDataPacket($pk, true);
    }

    /**
     * @param Player $player
     * @param Player $death
     * @return void
     */
    public function handleStreak(Player $player, Player $death): void
    {
        $KillSession = PracticeCore::getSessionManager()::getSession($player);
        $DeathSession = PracticeCore::getSessionManager()::getSession($death);
        $oldStreak = $DeathSession->getStreak();
        $newStreak = $KillSession->getStreak();
        if ($oldStreak > 5) {
            $death->sendMessage(PracticeCore::getPrefixCore() . '§r§aYour ' . $oldStreak . ' killstreak was ended by ' . $player->getName() . '!');
            $player->sendMessage(PracticeCore::getPrefixCore() . '§r§aYou have ended ' . $death->getName() . "'s " . $oldStreak . ' killstreaks!');
        }
        if ($newStreak % 5 === 0) {
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
                if ($entity instanceof Hologram || $entity instanceof JoinEntity) {
                    continue;
                }
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
        $array = [];
        foreach ($this->getPlayerSession() as $session) {
            $array[] = $session->getPlayer();
        }
        return $array;
    }

    /**
     * @return array<PlayerSession>
     */
    public function getPlayerSession(): array
    {
        return PracticeCore::getCaches()->PlayerSession;
    }

    /**
     * @param Player $player
     * @param string $message
     * @return string
     */
    public function getChatFormat(Player $player, string $message): string
    {
        $session = PracticeCore::getSessionManager()::getSession($player);
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
        $item3 = VanillaItems::GOLDEN_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item3->setCustomName('§r§bDuels');
        $player->getOffHandInventory()->clearAll();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->getInventory()->setItem(0, $item);
        $player->getInventory()->setItem(4, $item3);
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

    /**
     * @param Player $player
     * @return void
     */
    public function checkQueue(Player $player): void
    {
        $player->sendMessage(PracticeCore::getPrefixCore() . '§r§aYou have been entered into the queue!');
        $PSession = PracticeCore::getSessionManager()::getSession($player);
        foreach ($this->getPlayerInSession() as $players) {
            if ($players->getId() === $player->getId()) {
                continue;
            }
            $Qsession = PracticeCore::getSessionManager()::getSession($players);
            if ($PSession->isQueueing && $Qsession->isQueueing && $PSession->DuelKit === $Qsession->DuelKit) {
                $kit = $PSession->DuelKit;
                if ($kit !== null) {
                    PracticeCore::getInstance()->getDuelManager()->createMatch($player, $players, $kit);
                    $player->sendMessage(PracticeCore::getPrefixCore() . 'Found a match against §c' . $players->getName());
                    $players->sendMessage(PracticeCore::getPrefixCore() . 'Found a match against §c' . $player->getName());
                    $PSession->setOpponent($players->getName());
                    $Qsession->setOpponent($player->getName());
                    foreach ([$Qsession, $PSession] as $session) {
                        $session->isQueueing = false;
                    }
                }
            }
        }
    }

    /**
     * @param string $dirPath
     * @return void
     */
    public function deleteDir(string $dirPath): void
    {
        if (is_dir($dirPath)) {
            if (!str_ends_with($dirPath, '/')) {
                $dirPath .= '/';
            }
            $files = glob($dirPath . '*', GLOB_MARK);
            if (is_array($files)) {
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        $this->deleteDir($file);
                    } else {
                        unlink($file);
                    }
                }
                rmdir($dirPath);
            }
        }
    }
}
