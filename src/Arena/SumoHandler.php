<?php

declare(strict_types=1);

namespace Kohaku\Core\Arena;

use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ArenaUtils;
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
    public SumoScheduler $scheduler;
    public int $phase = 0;
    public array $data = [];
    public bool $setup = false;
    public array $players = [];
    public ?World $level;

    public function __construct(Loader $plugin, array $arenaFileData)
    {
        $this->plugin = $plugin;
        $this->data = $arenaFileData;
        $this->setup = !$this->enable(false);
        $this->plugin->getScheduler()->scheduleRepeatingTask($this->scheduler = new SumoScheduler($this), 20);
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
        } else if ($this->data["level"] == null) {
            return false;
        } else if (!Server::getInstance()->getWorldManager()->isWorldGenerated($this->data["level"])) {
            return false;
        } else {
            if (!Server::getInstance()->getWorldManager()->isWorldLoaded($this->data["level"]))
                Server::getInstance()->getWorldManager()->loadWorld($this->data["level"]);
            $this->level = Server::getInstance()->getWorldManager()->getWorldByName($this->data["level"]);
        }
        if (!is_int($this->data["slots"])) {
            return false;
        } else if (!is_array($this->data["spawns"])) {
            return false;
        } else if (count($this->data["spawns"]) != $this->data["slots"]) {
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
            $this->plugin->getLogger()->error("Can not load arena: Arena is not enabled!");
            return;
        } else if (!$restart) {
            $this->plugin->getServer()->getPluginManager()->registerEvents($this, $this->plugin);
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
            $player->sendMessage(Loader::getPrefixCore() . "§e The game is in configurating!");
            return;
        } else if (count($this->players) >= $this->data["slots"]) {
            $player->sendMessage(Loader::getPrefixCore() . "§eThe game is full!");
            return;
        } else if ($this->inGame($player)) {
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
        $player->setImmobile(true);
        $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 99999999 * 20, 10, false));
        $player->setGamemode(Gamemode::ADVENTURE());
        $player->setHealth(20);
    }

    public function inGame(Player $player): bool
    {
        switch ($this->phase) {
            case self::PHASE_LOBBY:
                $inGame = false;
                foreach ($this->players as $players) {
                    if ($players->getId() == $player->getId()) {
                        $inGame = true;
                    }
                }
                return $inGame;
            default:
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

    public function startRestart()
    {
        $player = null;
        foreach ($this->players as $p) {
            $player = $p;
        }
        if (!$player instanceof Player || (!$player->isOnline())) {
            $this->phase = self::PHASE_RESTART;
            return;
        }
        $this->plugin->getServer()->broadcastMessage(Loader::getPrefixCore() . "§r§ePlayer {$player->getName()} won the Sumo!");
        $this->phase = self::PHASE_RESTART;
    }

    public function checkEnd(): bool
    {
        return count($this->players) <= 1;
    }

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
                        if ($p->getId() == $player->getId()) {
                            $index = $i;
                        }
                    }
                    if ($player->getPosition()->asVector3()->distance(new Vector3($this->data["spawns"][$index]["x"], $this->data["spawns"][$index]["y"], $this->data["spawns"][$index]["z"])) > 1) {
                        $player->teleport(new Vector3($this->data["spawns"][$index]["x"], $this->data["spawns"][$index]["y"], $this->data["spawns"][$index]["z"]));
                    }
                }
            }
        }
    }

    public function disconnectPlayer(Player $player, string $quitMsg = "You left the Game")
    {
        switch ($this->phase) {
            case SumoHandler::PHASE_LOBBY:
                $index = "";
                foreach ($this->players as $i => $p) {
                    if ($p->getId() == $player->getId()) {
                        $index = $i;
                    }
                }
                if ($index != "") {
                    unset($this->players[$index]);
                }
                break;
            default:
                unset($this->players[$player->getName()]);
                break;
        }
        $player->getEffects()->clear();
        $player->setGamemode($this->plugin->getServer()->getGamemode());
        $player->setHealth(20);
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->setImmobile(false);
        ArenaUtils::getInstance()->addDeath($player);
        ArenaUtils::getInstance()->GiveItem($player);
        $player->teleport($this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
        $player->sendMessage(Loader::getPrefixCore() . "§r§e$quitMsg");
    }

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
