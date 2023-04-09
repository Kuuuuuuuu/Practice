<?php

declare(strict_types=1);

namespace Nayuki;

use InvalidArgumentException;
use Nayuki\Misc\PracticeChunkLoader;
use pocketmine\block\VanillaBlocks;
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
use pocketmine\world\World;

use function is_dir;
use function rmdir;
use function unlink;

final class PracticeUtils
{
    /**
     * @param string $dirPath
     * @return void
     */
    public function deleteDir(string $dirPath): void
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        $dirPath = rtrim($dirPath, '/') . '/';
        $files = glob($dirPath . '*', GLOB_MARK);
        if ($files === false) {
            throw new InvalidArgumentException("Could not read contents of $dirPath");
        }
        foreach ($files as $file) {
            is_dir($file) ? $this->deleteDir($file) : unlink($file);
        }
        rmdir($dirPath);
    }

    /**
     * @param Player $player
     * @param World $world
     * @return void
     */
    public function teleportToArena(Player $player, World $world): void
    {
        $position = $world->getSpawnLocation();
        $this->onChunkGenerated($position->getWorld(), (int)$position->getX() >> 4, (int)$position->getZ() >> 4, function () use ($player, $position) {
            $player->teleport($position);
        });
    }

    /**
     * @param World $world
     * @param int $x
     * @param int $z
     * @param callable $callable
     * @return void
     */
    public function onChunkGenerated(World $world, int $x, int $z, callable $callable): void
    {
        if ($world->isChunkPopulated($x, $z)) {
            ($callable)();
            return;
        }
        $world->registerChunkLoader(new PracticeChunkLoader($world, $x, $z, $callable), $x, $z);
    }

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
        $KillSession = PracticeCore::getSessionManager()->getSession($player);
        $DeathSession = PracticeCore::getSessionManager()->getSession($death);
        $oldStreak = $DeathSession->getStreak();
        $newStreak = $KillSession->getStreak();
        if ($oldStreak > 5) {
            $deathMessage = PracticeCore::getPrefixCore() . '§r§aYour ' . $oldStreak . ' killstreak was ended by ' . $player->getName() . '!';
            $playerMessage = PracticeCore::getPrefixCore() . '§r§aYou have ended ' . $death->getName() . "'s " . $oldStreak . ' killstreaks!';
            $death->sendMessage($deathMessage);
            $player->sendMessage($playerMessage);
        }
        if ($newStreak % 5 == 0) {
            $broadcastMessage = PracticeCore::getPrefixCore() . '§r§a' . $player->getName() . ' is on a ' . $newStreak . ' killstreaks!';
            Server::getInstance()->broadcastMessage($broadcastMessage);
        }
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
        $item4 = VanillaBlocks::CHEST()->asItem()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10));
        $item4->setCustomName('§r§bCosmetics');
        $player->getOffHandInventory()->clearAll();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->getInventory()->setItem(0, $item);
        $player->getInventory()->setItem(4, $item3);
        $player->getInventory()->setItem(8, $item2);
        $player->getInventory()->setItem(7, $item4);
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
        $sessionManager = PracticeCore::getSessionManager();
        $sessions = $sessionManager->getSessions();
        $PSession = $sessionManager->getSession($player);
        foreach ($sessions as $Qsession) {
            $players = $Qsession->getPlayer();
            if ($players->getId() === $player->getId()) {
                continue;
            }
            if ($PSession->DuelKit !== $Qsession->DuelKit) {
                continue;
            }
            if (!$PSession->isQueueing || !$Qsession->isQueueing) {
                continue;
            }
            $kit = $PSession->DuelKit;
            if ($kit === null) {
                continue;
            }
            PracticeCore::getInstance()->getDuelManager()->createMatch($player, $players, $kit);
            $player->sendMessage(PracticeCore::getPrefixCore() . 'Found a match against §c' . $players->getName());
            $players->sendMessage(PracticeCore::getPrefixCore() . 'Found a match against §c' . $player->getName());
            $PSession->setOpponent($players->getName());
            $Qsession->setOpponent($player->getName());
            $PSession->isQueueing = false;
            $Qsession->isQueueing = false;
            return;
        }
    }

    /**
     * @return int
     */
    public function randomCoinsPerKill(): int
    {
        return mt_rand(1, 10);
    }
}
