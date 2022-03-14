<?php

declare(strict_types=1);

namespace Kohaku\Core\Arena;

use JsonException;
use Kohaku\Core\Loader;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class ArenaFactory
{

    public function getPlayers(mixed $arena): string
    {
        if (!Server::getInstance()->getWorldManager()->getWorldByName($arena)) {
            return "§4Unknown Arena";
        } else {
            return (string)count(Server::getInstance()->getWorldManager()->getWorldByName($arena)->getPlayers());
        }
    }

    public function getResistanceArena()
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        return $data->get("Resistance");
    }

    public function getFistArena()
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        return $data->get("Fist");
    }

    public function getBoxingArena()
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        return $data->get("Boxing");
    }

    public function getParkourArena()
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        return $data->get("Parkour");
    }

    public function getComboArena()
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        return $data->get("Combo");
    }

    public function getKnockbackArena()
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        return $data->get("Knockback");
    }

    public function getKitPVPArena()
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        return $data->get("KitPVP");
    }

    /**
     * @throws JsonException
     */

    public function setFistArena(Player $player, string $world)
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        $data->set("Fist", $world);
        $data->save();
        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§aThe Arena was saved");
    }

    /**
     * @throws JsonException
     */
    public function setResistanceArena(Player $player, string $world)
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        $data->set("Resistance", $world);
        $data->save();
        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§aThe Arena was saved");
    }

    /**
     * @throws JsonException
     */

    public function setKitPVPArena(Player $player, string $world)
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        $data->set("KitPVP", $world);
        $data->save();
        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§aThe Arena was saved");
    }

    /**
     * @throws JsonException
     */

    public function setBoxingArena(Player $player, string $world)
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        $data->set("Boxing", $world);
        $data->save();
        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§aThe Arena was saved");
    }

    /**
     * @throws JsonException
     */

    public function setParkourArena(Player $player, string $world)
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        $data->set("Parkour", $world);
        $data->save();
        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§aThe Arena was saved");
    }

    /**
     * @throws JsonException
     */
    public function setComboArena(Player $player, string $world)
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        $data->set("Combo", $world);
        $data->save();
        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§aThe Arena was saved");
    }

    /**
     * @throws JsonException
     */
    public function setKnockbackArena(Player $player, string $world)
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        $data->set("Knockback", $world);
        $data->save();
        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§aThe Arena was saved");
    }

    /**
     * @throws JsonException
     */
    public function removeFist(Player $player)
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        $data->remove("Fist");
        $data->save();
        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "Fist removed arena");
    }

    /**
     * @throws JsonException
     */
    public function removeKnockback(Player $player)
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        $data->remove("Knockback");
        $data->save();
        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "Knockback removed arena");
    }

    /**
     * @throws JsonException
     */
    public function removeParkour(Player $player)
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        $data->remove("Parkour");
        $data->save();
        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "Parkour removed arena");
    }

    /**
     * @throws JsonException
     */
    public function removeBoxing(Player $player)
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        $data->remove("Boxing");
        $data->save();
        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "Boxing removed arena");
    }

    /**
     * @throws JsonException
     */
    public function removeResistance(Player $player)
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        $data->remove("Resistance");
        $data->save();
        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "Resistance removed arena");
    }

    /**
     * @throws JsonException
     */
    public function removeCombo(Player $player)
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        $data->remove("Combo");
        $data->save();
        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "Combo removed arena");
    }

    /**
     * @throws JsonException
     */
    public function removeKitPVP(Player $player)
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . "data/arenas.yml", Config::YAML);
        $data->remove("KitPVP");
        $data->save();
        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "KitPVP removed arena");
    }
}