<?php

declare(strict_types=1);

namespace Kohaku\Core\Arena;

use Kohaku\Core\Loader;
use Kohaku\Core\Utils\MapReset;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\tile\Chest;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;

class SkywarsHandler implements Listener
{

    const PHASE_LOBBY = 0;
    const PHASE_GAME = 1;
    const PHASE_RESTART = 2;

    public Loader $plugin;
    public SkywarsScheduler $scheduler;
    public MapReset $mapReset;
    public int $phase = 0;
    public array $data = [];
    public bool $setup = false;
    public array $players = [];
    public ?World $level = null;

    public function __construct(Loader $plugin, array $arenaFileData)
    {
        $this->plugin = $plugin;
        $this->data = $arenaFileData;
        $this->setup = !$this->enable(false);
        $this->plugin->getScheduler()->scheduleRepeatingTask($this->scheduler = new SkywarsScheduler($this), 20);
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
        if (!$this->plugin->getServer()->getWorldManager()->isWorldGenerated($this->data["level"])) {
            return false;
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
        if (!is_array($this->data["joinsign"])) {
            return false;
        }
        if (count($this->data["joinsign"]) !== 2) {
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
            $this->plugin->getLogger()->error("Can not load arena: SkywarsHandler is not enabled!");
            return;
        }
        $this->mapReset = MapReset::getInstance();
        if (!$restart) {
            $this->plugin->getServer()->getPluginManager()->registerEvents($this, $this->plugin);
        } else {
            $this->scheduler->reloadTimer();
            $this->level = $this->mapReset->loadMap($this->data["level"]);
        }
        if (!$this->level instanceof World) {
            $level = $this->mapReset->loadMap($this->data["level"]);
            if (!$level instanceof World) {
                $this->plugin->getLogger()->error("SkywarsHandler level wasn't found. Try save level in setup mode.");
                $this->setup = true;
                return;
            }
            $this->level = $level;
        }
        $this->phase = static::PHASE_LOBBY;
        $this->players = [];
    }

    private function createBasicData()
    {
        $this->data = [
            "level" => null,
            "slots" => 12,
            "spawns" => [],
            "enabled" => false,
            "joinsign" => []
        ];
    }

    public function startGame()
    {
        $players = [];
        foreach ($this->players as $player) {
            $players[$player->getName()] = $player;
            $player->setGamemode($player::SURVIVAL);
        }
        $this->players = $players;
        $this->phase = 1;
        $this->fillChests();
    }

    public function fillChests(): void
    {
        $contents = $this->getChestContents();
        $level = $this->level;
        foreach ($level->getLoadedChunks() as $chunk) {
            foreach ($chunk->getTiles() as $tile) {
                if ($tile instanceof Chest) {
                    $inventory = $tile->getInventory();
                    $inventory->clearAll();
                    if (empty($contents)) {
                        $contents = $this->getChestContents();
                    }
                    foreach (array_shift($contents) as $key => $val) {
                        $inventory->setItem($key, ItemFactory::getInstance()->get($val[0], 0, $val[1]));
                    }
                    $inventory->setContents($inventory->getViewers());
                }
            }
        }
    }

    private function getChestContents(): array
    {
        $items = [
            "armor" => [
                [
                    ItemIds::LEATHER_CAP,
                    ItemIds::LEATHER_TUNIC,
                    ItemIds::LEATHER_PANTS,
                    ItemIds::LEATHER_BOOTS
                ],
                [
                    ItemIds::GOLD_HELMET,
                    ItemIds::GOLD_CHESTPLATE,
                    ItemIds::GOLD_LEGGINGS,
                    ItemIds::GOLD_BOOTS
                ],
                [
                    ItemIds::CHAIN_HELMET,
                    ItemIds::CHAIN_CHESTPLATE,
                    ItemIds::CHAIN_LEGGINGS,
                    ItemIds::CHAIN_BOOTS
                ],
                [
                    ItemIds::IRON_HELMET,
                    ItemIds::IRON_CHESTPLATE,
                    ItemIds::IRON_LEGGINGS,
                    ItemIds::IRON_BOOTS
                ],
                [
                    ItemIds::DIAMOND_HELMET,
                    ItemIds::DIAMOND_CHESTPLATE,
                    ItemIds::DIAMOND_LEGGINGS,
                    ItemIds::DIAMOND_BOOTS
                ]
            ],
            "weapon" => [
                [
                    ItemIds::WOODEN_SWORD,
                    ItemIds::WOODEN_AXE,
                ],
                [
                    ItemIds::GOLD_SWORD,
                    ItemIds::GOLD_AXE
                ],
                [
                    ItemIds::STONE_SWORD,
                    ItemIds::STONE_AXE
                ],
                [
                    ItemIds::IRON_SWORD,
                    ItemIds::IRON_AXE
                ],
                [
                    ItemIds::DIAMOND_SWORD,
                    ItemIds::DIAMOND_AXE
                ]
            ],
            "food" => [
                [
                    ItemIds::RAW_PORKCHOP,
                    ItemIds::RAW_CHICKEN,
                    ItemIds::MELON_SLICE,
                    ItemIds::COOKIE
                ],
                [
                    ItemIds::RAW_BEEF,
                    ItemIds::CARROT
                ],
                [
                    ItemIds::APPLE,
                    ItemIds::GOLDEN_APPLE
                ],
                [
                    ItemIds::BEETROOT_SOUP,
                    ItemIds::BREAD,
                    ItemIds::BAKED_POTATO
                ],
                [
                    ItemIds::MUSHROOM_STEW,
                    ItemIds::COOKED_CHICKEN
                ],
                [
                    ItemIds::COOKED_PORKCHOP,
                    ItemIds::STEAK,
                    ItemIds::PUMPKIN_PIE
                ],
            ],
            "throwable" => [
                [
                    ItemIds::BOW,
                    ItemIds::ARROW
                ],
                [
                    ItemIds::SNOWBALL
                ],
                [
                    ItemIds::EGG
                ]
            ],
            "block" => [
                ItemIds::STONE,
                ItemIds::WOODEN_PLANKS,
                ItemIds::SANDSTONE,
                ItemIds::STONEBRICK,
                ItemIds::COBBLESTONE,
                ItemIds::DIRT
            ],
            "other" => [
                [
                    ItemIds::WOODEN_PICKAXE,
                    ItemIds::GOLD_PICKAXE,
                    ItemIds::STONE_PICKAXE,
                    ItemIds::IRON_PICKAXE,
                    ItemIds::DIAMOND_PICKAXE
                ],
                [
                    ItemIds::STICK,
                    ItemIds::STRING
                ]
            ]
        ];

        $templates = [];
        for ($i = 0; $i < 10; $i++) {
            $armorq = mt_rand(0, 1);
            $armortype = $items["armor"][array_rand($items["armor"])];
            $armor1 = [$armortype[array_rand($armortype)], 1];
            if ($armorq) {
                $armortype = $items["armor"][array_rand($items["armor"])];
                $armor2 = [$armortype[array_rand($armortype)], 1];
            } else {
                $armor2 = [0, 1];
            }
            $weapontype = $items["weapon"][array_rand($items["weapon"])];
            $weapon = [$weapontype[array_rand($weapontype)], 1];
            $ftype = $items["food"][array_rand($items["food"])];
            $food = [$ftype[array_rand($ftype)], mt_rand(2, 5)];
            if (mt_rand(0, 1)) {
                $tr = $items["throwable"][array_rand($items["throwable"])];
                if (count($tr) === 2) {
                    $throwable1 = [$tr[1], mt_rand(10, 20)];
                    $throwable2 = [$tr[0], 1];
                } else {
                    $throwable1 = [0, 1];
                    $throwable2 = [$tr[0], mt_rand(5, 10)];
                }
                $other = [0, 1];
            } else {
                $throwable1 = [0, 1];
                $throwable2 = [0, 1];
                $ot = $items["other"][array_rand($items["other"])];
                $other = [$ot[array_rand($ot)], 1];
            }
            $block = [$items["block"][array_rand($items["block"])], 64];
            $contents = [
                $armor1,
                $armor2,
                $weapon,
                $food,
                $throwable1,
                $throwable2,
                $block,
                $other
            ];
            shuffle($contents);
            $fcontents = [
                mt_rand(0, 1) => array_shift($contents),
                mt_rand(2, 4) => array_shift($contents),
                mt_rand(5, 9) => array_shift($contents),
                mt_rand(10, 14) => array_shift($contents),
                mt_rand(15, 16) => array_shift($contents),
                mt_rand(17, 19) => array_shift($contents),
                mt_rand(20, 24) => array_shift($contents),
                mt_rand(25, 26) => array_shift($contents),
            ];
            $templates[] = $fcontents;
        }
        shuffle($templates);
        return $templates;
    }

    public function startRestart()
    {
        $player = null;
        foreach ($this->players as $p) {
            $player = $p;
        }
        if ((!$player instanceof Player) || (!$player->isOnline())) {
            $this->phase = self::PHASE_RESTART;
            return;
        }
        $this->plugin->getServer()->broadcastMessage(Loader::getPrefixCore() . "Player {$player->getName()} has won the Skywars!");
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
                        if ($p->getId() === $player->getId()) {
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

    public function disconnectPlayer(Player $player, bool $death = false)
    {
        if ($this->phase === self::PHASE_LOBBY) {
            $index = "";
            foreach ($this->players as $i => $p) {
                if ($p->getId() === $player->getId()) {
                    $index = $i;
                }
            }
            if ($index != "") {
                unset($this->players[$index]);
            }
        } else {
            unset($this->players[$player->getName()]);
        }
        $player->getEffects()->clear();
        $player->setGamemode($this->plugin->getServer()->getGamemode());
        $player->setHealth(20);
        $player->getHungerManager()->setFood(20);
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->teleport($this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
        if (!$death) {
            Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "{$player->getName()} left the game. §7[" . count($this->players) . "/{$this->data["slots"]}]");
        }
    }

    public function onInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        if ($this->inGame($player) && $event->getBlock()->getId() === BlockLegacyIds::CHEST && $this->phase == self::PHASE_LOBBY) {
            $event->cancel();
            return;
        }
        if ($this->phase == self::PHASE_GAME) {
            $player->sendMessage(Loader::getPrefixCore() . "Arena is in-game");
            return;
        }
        if ($this->phase == self::PHASE_RESTART) {
            $player->sendMessage(Loader::getPrefixCore() . "Arena is restarting!");
            return;
        }
        if ($this->setup) {
            return;
        }
        $this->joinToArena($player);
    }

    public function joinToArena(Player $player)
    {
        if (!$this->data["enabled"]) {
            $player->sendMessage(Loader::getPrefixCore() . "Arena is under setup!");
            return;
        }
        if (count($this->players) >= $this->data["slots"]) {
            $player->sendMessage(Loader::getPrefixCore() . "Arena is full!");
            return;
        }
        if ($this->inGame($player)) {
            $player->sendMessage(Loader::getPrefixCore() . "You are already in game!");
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
        $player->setGamemode(GameMode::ADVENTURE());
        $player->setHealth(20);
        $player->getHungerManager()->setFood(20);
        Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "{$player->getName()} joined the game! §7[" . count($this->players) . "/{$this->data["slots"]}]");
    }

    public function onDeath(PlayerDeathEvent $event)
    {
        $player = $event->getPlayer();
        if (!$this->inGame($player)) return;
        foreach ($event->getDrops() as $item) {
            $player->getWorld()->dropItem($player->getPosition()->asVector3(), $item);
        }
        $this->disconnectPlayer($player, true);
        $event->setDeathMessage("");
        $event->setDrops([]);
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        if ($this->inGame($event->getPlayer())) {
            $this->disconnectPlayer($event->getPlayer());
        }
    }

    public function __destruct()
    {
        unset($this->scheduler);
    }
}
