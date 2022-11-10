<?php

declare(strict_types=1);

namespace Kuu\Arena;

use JsonException;
use Kuu\PracticeConfig;
use Kuu\PracticeCore;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\World;

class ArenaFactory
{

    /**
     * @param mixed $arena
     * @return string
     */
    public function getPlayers(mixed $arena): string
    {
        if (is_string($arena)) {
            $world = Server::getInstance()->getWorldManager()->getWorldByName($arena);
            if ($world instanceof World) {
                return (string)count($world->getPlayers());
            }
        }
        return 'Error: Unknown arena';
    }

    /**
     * @return string
     */
    public function getResistanceArena(): string
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Resistance');
    }

    /**
     * @return string
     */
    public function getFistArena(): string
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Fist');
    }

    /**
     * @return string
     */
    public function getOITCArena(): string
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('OITC');
    }

    /**
     * @return string
     */
    public function getBuildArena(): string
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Build');
    }

    /**
     * @return string
     */
    public function getParkourArena(): string
    {
        $data = new Config (PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Parkour');
    }

    /**
     * @return string
     */
    public function getBoxingArena(): string
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Boxing');
    }

    /**
     * @return string
     */
    public function getComboArena(): string
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Combo');
    }

    /**
     * @return string
     */
    public function getKnockbackArena(): string
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Knockback');
    }

    /**
     * @param Player $player
     * @param string $world
     * @return void
     * @throws JsonException
     */

    public function setFistArena(Player $player, string $world): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Fist', $world);
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @param Player $player
     * @param string $world
     * @return void
     * @throws JsonException
     */

    public function setParkourArena(Player $player, string $world): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Parkour', $world);
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @return array
     */
    public function getRandomSpawnOitc(): array
    {
        $random = array_rand(PracticeConfig::OITCSpawns);
        return PracticeConfig::OITCSpawns[$random];
    }

    /**
     * @return array
     */
    public function getRandomSpawnBuild(): array
    {
        $random = array_rand(PracticeConfig::BuildFFASpawns);
        return PracticeConfig::BuildFFASpawns[$random];
    }

    /**
     * @param Player $player
     * @param string $world
     * @return void
     * @throws JsonException
     */

    public function setOITCArena(Player $player, string $world): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('OITC', $world);
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @param Player $player
     * @param string $world
     * @return void
     * @throws JsonException
     */

    public function setBuildArena(Player $player, string $world): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Build', $world);
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @param Player $player
     * @param string $world
     * @return void
     * @throws JsonException
     */
    public function setResistanceArena(Player $player, string $world): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Resistance', $world);
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @param Player $player
     * @param string $world
     * @return void
     * @throws JsonException
     */

    public function setBoxingArena(Player $player, string $world): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Boxing', $world);
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @param Player $player
     * @param string $world
     * @return void
     * @throws JsonException
     */
    public function setComboArena(Player $player, string $world): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Combo', $world);
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @param Player $player
     * @param string $world
     * @return void
     * @throws JsonException
     */
    public function setKnockbackArena(Player $player, string $world): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Knockback', $world);
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @param Player $player
     * @return void
     * @throws JsonException
     */
    public function removeOITC(Player $player): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('OITC');
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . 'Removed arena');
    }

    /**
     * @param Player $player
     * @return void
     * @throws JsonException
     */
    public function removeBuild(Player $player): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Build');
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . 'Removed arena');
    }

    /**
     * @param Player $player
     * @return void
     * @throws JsonException
     */
    public function removeParkour(Player $player): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Parkour');
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . 'Removed arena');
    }

    /**
     * @param Player $player
     * @return void
     * @throws JsonException
     */
    public function removeFist(Player $player): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Fist');
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . 'Removed arena');
    }

    /**
     * @param Player $player
     * @return void
     * @throws JsonException
     */
    public function removeKnockback(Player $player): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Knockback');
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . 'Removed arena');
    }

    /**
     * @param Player $player
     * @return void
     * @throws JsonException
     */
    public function removeBoxing(Player $player): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Boxing');
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . 'Removed arena');
    }

    /**
     * @param Player $player
     * @return void
     * @throws JsonException
     */
    public function removeResistance(Player $player): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Resistance');
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . 'Removed arena');
    }

    /**
     * @param Player $player
     * @return void
     * @throws JsonException
     */
    public function removeCombo(Player $player): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Combo');
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . 'Removed arena');
    }
}