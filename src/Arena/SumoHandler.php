<?php

declare(strict_types=1);

namespace Kohaku\Arena;

use Exception;
use Kohaku\Loader;
use Kohaku\NeptunePlayer;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;

class SumoHandler implements Listener
{

    public const PHASE_LOBBY = 0;
    public const PHASE_GAME = 1;
    public const PHASE_RESTART = 2;

    public Loader $plugin;
    public int $phase = 0;
    public array $data = [];
    public bool $setup = false;
    public array $players = [];
    public ?World $level;
    private SumoScheduler $scheduler;

    public function __construct(array $arenaFileData)
    {
        $this->data = $arenaFileData;
        $this->setup = !$this->enable(false);
        Loader::getInstance()->getScheduler()->scheduleRepeatingTask($this->scheduler = new SumoScheduler($this), 1);
        if ($this->setup) {
            if (empty($this->data)) {
                $this->createBasicData();
            }
        } else {
            $this->loadArena();
        }
    }

    public function enable(bool $loadArena = true): bool
    {
        if (empty($this->data)) {
            return false;
        }
        if ($this->data["level"] == null) {
            return false;
        }
        if (!Server::getInstance()->getWorldManager()->isWorldGenerated($this->data["level"])) {
            return false;
        } else {
            if (!Server::getInstance()->getWorldManager()->isWorldLoaded($this->data["level"]))
                Server::getInstance()->getWorldManager()->loadWorld($this->data["level"]);
            $this->level = Server::getInstance()->getWorldManager()->getWorldByName($this->data["level"]);
        }
        if (!is_int($this->data["slots"])) {
            return false;
        }
        if (!is_array($this->data["spawns"])) {
            return false;
        }
        if (count($this->data["spawns"]) != $this->data["slots"]) {
            return false;
        }
        $this->data["enabled"] = true;
        $this->setup = false;
        if ($loadArena) $this->loadArena();
        return true;
    }

    public function loadArena(bool $restart = false)
    {
        if (!$this->data["enabled"]) {
            Loader::getInstance()->getLogger()->error("Can not load arena: Arena is not enabled!");
            return;
        } elseif (!$restart) {
            Server::getInstance()->getPluginManager()->registerEvents($this, Loader::getInstance());
            if (!Server::getInstance()->getWorldManager()->isWorldLoaded($this->data["level"])) {
                Server::getInstance()->getWorldManager()->loadWorld($this->data["level"]);
            }
            $this->level = Server::getInstance()->getWorldManager()->getWorldByName($this->data["level"]);
        } else {
            $this->scheduler->reloadTimer();
        }
        $this->level = Server::getInstance()->getWorldManager()->getWorldByName($this->data["level"]);
        $this->phase = static::PHASE_LOBBY;
        $this->players = [];
    }

    private function createBasicData()
    {
        $this->data = [
            "level" => null,
            "slots" => 2,
            "spawns" => [],
            "enabled" => false
        ];
    }

    public function joinToArena(Player $player)
    {
        if (!$this->data["enabled"]) {
            $player->sendMessage(Loader::getPrefixCore() . "§eThe game is in configurating!");
            return;
        } elseif (count($this->players) >= $this->data["slots"]) {
            $player->sendMessage(Loader::getPrefixCore() . "§eThe game is full!");
            return;
        } elseif ($this->inGame($player)) {
            $player->sendMessage(Loader::getPrefixCore() . "You are already in the queue/game!");
            return;
        }
        $selected = false;
        for ($lS = 1; $lS <= $this->data["slots"]; $lS++) {
            if (!$selected) {
                if (!isset($this->players[$index = "spawn-$lS"])) {
                    $player->teleport(Position::fromObject(new Vector3($this->data["spawns"][$index]["x"], $this->data["spawns"][$index]["y"], $this->data["spawns"][$index]["z"]), $this->level));
                    $this->players[$index] = $player;
                    $selected = true;
                }
            }
        }
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->setImmobile();
        $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 99999999 * 20, 10, false));
        $player->setGamemode(Gamemode::ADVENTURE());
        $player->setHealth(20);
    }

    public function inGame(Player $player): bool
    {
        if ($this->phase === static::PHASE_LOBBY) {
            $inGame = false;
            foreach ($this->players as $players) {
                if ($players->getId() === $player->getId()) {
                    $inGame = true;
                }
            }
            return $inGame;
        } else {
            return isset($this->players[$player->getName()]);
        }
    }

    public function startGame()
    {
        $players = [];
        foreach ($this->players as $player) {
            $players[$player->getName()] = $player;
        }
        $this->players = $players;
        $this->phase = 1;
    }

    /**
     * @throws Exception
     */
    public function startRestart()
    {
        $player = null;
        foreach ($this->players as $p) {
            $player = $p;
        }
        if ($player instanceof Player and $player->isOnline()) {
            Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "§r§ePlayer {$player->getName()} won the Sumo!");
            $player->sendMessage(Loader::getPrefixCore() . "§r§eYou got " . Loader::getInstance()->getArenaUtils()->getData($player->getName())->addElo() . " Elos!");
        }
        $this->phase = self::PHASE_RESTART;
    }

    public function checkEnd(): bool
    {
        return count($this->players) <= 1;
    }

    /**
     * @throws Exception
     */
    public function onMove(PlayerMoveEvent $event)
    {
        if ($this->phase === self::PHASE_LOBBY) {
            $player = $event->getPlayer();
            if ($this->inGame($player)) {
                if ($player->getWorld() !== $this->level) {
                    $this->disconnectPlayer($player);
                } else {
                    $index = null;
                    foreach ($this->players as $i => $p) {
                        if ($p->getId() === $player->getId()) {
                            $index = $i;
                        }
                    }
                    if ($player->getPosition()->asVector3()->distance(new Vector3($this->data["spawns"][$index]["x"], $this->data["spawns"][$index]["y"], $this->data["spawns"][$index]["z"])) > 0.5) {
                        $player->teleport(new Vector3($this->data["spawns"][$index]["x"], $this->data["spawns"][$index]["y"], $this->data["spawns"][$index]["z"]));
                    }
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function disconnectPlayer(Player $player)
    {
        if ($this->phase === self::PHASE_LOBBY) {
            $index = "";
            foreach ($this->players as $i => $p) {
                if ($p->getId() === $player->getId()) {
                    $index = $i;
                }
            }
            if ($index !== "") {
                unset($this->players[$index]);
            }
        } else {
            unset($this->players[$player->getName()]);
        }
        $player->getEffects()->clear();
        $player->setGamemode(Loader::getInstance()->getServer()->getGamemode());
        $player->setHealth(20);
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->setImmobile(false);
        Loader::getInstance()->getArenaUtils()->GiveItem($player);
        $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
    }

    /**
     * @throws Exception
     */
    public function onLeft(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        if ($this->inGame($player)) {
            $this->disconnectPlayer($player);
        }
    }

    public function __destruct()
    {
        unset($this->scheduler);
    }
}