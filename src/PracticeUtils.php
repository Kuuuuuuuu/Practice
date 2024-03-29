<?php

declare(strict_types=1);

namespace Nayuki;

use InvalidArgumentException;
use Nayuki\Misc\PracticeChunkLoader;
use pocketmine\block\utils\DyeColor;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
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
        $sessionManager = PracticeCore::getSessionManager();
        $killSession = $sessionManager->getSession($player);
        $deathSession = $sessionManager->getSession($death);

        $oldStreak = $deathSession->getStreak();
        $newStreak = $killSession->getStreak();

        if ($oldStreak > 5) {
            $deathMessage = PracticeCore::getPrefixCore() . TextFormat::GREEN . 'Your ' . $oldStreak . ' killstreak was ended by ' . $player->getName() . '!';
            $playerMessage = PracticeCore::getPrefixCore() . TextFormat::GREEN . 'You have ended ' . $death->getName() . "'s " . $oldStreak . ' killstreaks!';

            $death->sendMessage($deathMessage);
            $player->sendMessage($playerMessage);
        }

        if ($newStreak % 5 === 0) {
            $broadcastMessage = PracticeCore::getPrefixCore() . TextFormat::GREEN . $player->getName() . ' is on a ' . $newStreak . ' killstreaks!';
            Server::getInstance()->broadcastMessage($broadcastMessage);
        }
    }

    /**
     * @param Player $player
     * @return void
     */
    public function teleportToLobby(Player $player): void
    {
        $worldManager = Server::getInstance()->getWorldManager();
        $world = $worldManager->getDefaultWorld();

        if ($world === null) {
            return;
        }

        $sessionManager = PracticeCore::getSessionManager();
        $scoreboardManager = PracticeCore::getScoreboardManager();
        $session = $sessionManager->getSession($player);

        $itemPlay = VanillaItems::DIAMOND_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->setCustomName('§r§dPlay');
        $itemSettings = VanillaItems::COMPASS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->setCustomName('§r§dSettings');
        $itemDuels = VanillaItems::GOLDEN_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->setCustomName('§r§dDuels');
        $itemCosmetics = VanillaItems::FEATHER()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->setCustomName('§r§dCosmetics');
        $itemBot = VanillaItems::IRON_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->setCustomName('§r§dBot');
        $itemSpectate = VanillaItems::DYE()->setColor(DyeColor::YELLOW)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10))->setCustomName('§r§dSpectate');

        $position = $world->getSpawnLocation();

        $player->getOffHandInventory()->clearAll();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setOnFire(0);
        $player->setHealth(20);
        $player->setScale(1);
        $player->teleport($position);
        $player->setGamemode(GameMode::SURVIVAL);

        $session->spectating = false;
        $session->spectatingDuel = null;
        $session->isDueling = false;
        $session->DuelKit = null;
        $session->BoxingPoint = 0;
        $session->DuelClass = null;
        $session->setOpponent(null);
        $session->isCombat = false;
        $session->CombatTime = 0;
        $session->isQueueing = false;

        $scoreboardManager->setLobbyScoreboard($player);
        $player->getInventory()->setItem(0, $itemPlay);
        $player->getInventory()->setItem(4, $itemDuels);
        $player->getInventory()->setItem(8, $itemSettings);
        $player->getInventory()->setItem(7, $itemCosmetics);
        $player->getInventory()->setItem(1, $itemBot);
        $player->getInventory()->setItem(5, $itemSpectate);
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
        $prefix = PracticeCore::getPrefixCore();
        $player->sendMessage($prefix . TextFormat::GREEN . 'You have been entered into the queue!');

        $sessionManager = PracticeCore::getSessionManager();
        $PSession = $sessionManager->getSession($player);
        $playerId = $player->getId();
        $playerName = $player->getName();
        $duelManager = PracticeCore::getInstance()->getDuelManager();

        foreach ($sessionManager->getSessions() as $Qsession) {
            $kit = $PSession->DuelKit;

            if ($kit === null || !$PSession->isQueueing || !$Qsession->isQueueing) {
                continue;
            }

            $opponent = $Qsession->getPlayer();

            if ($opponent->getId() === $playerId || $PSession->DuelKit !== $Qsession->DuelKit) {
                continue;
            }

            $duelManager->createMatch($player, $opponent, $kit);
            $player->sendMessage($prefix . 'Found a match against §c' . $opponent->getName());
            $opponent->sendMessage($prefix . 'Found a match against §c' . $playerName);

            $PSession->setOpponent($opponent->getName());
            $Qsession->setOpponent($playerName);
            $PSession->isQueueing = false;
            $Qsession->isQueueing = false;
        }
    }

    /**
     * @return int
     */
    public function randomCoinsPerKill(): int
    {
        return mt_rand(3, 20);
    }
}
