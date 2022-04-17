<?php

declare(strict_types=1);

namespace Kohaku\Arena;

use Exception;
use JsonException;
use Kohaku\Loader;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class ArenaFactory
{

    public function getPlayers(mixed $arena): string
    {
        try {
            return (string)count(Server::getInstance()->getWorldManager()->getWorldByName($arena)->getPlayers()) ?? 'Error';
        } catch (Exception) {
            return 'Error';
        }
    }

    public function getResistanceArena(): string
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Resistance');
    }

    public function getSkywarsArena(): string
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Skywars');
    }

    public function getFistArena(): string
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Fist');
    }

    public function getSumoDArena(): string
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('SumoD');
    }

    public function getOITCArena(): string
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('OITC');
    }

    public function getBuildArena(): string
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Build');
    }

    public function getBoxingArena(): string
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Boxing');
    }

    public function getBotArena(): string
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Bot');
    }

    public function getComboArena(): string
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Combo');
    }

    public function getKnockbackArena(): string
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Knockback');
    }

    public function getKitPVPArena(): string
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('KitPVP');
    }

    /**
     * @throws JsonException
     */

    public function setFistArena(Player $player, string $world): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Fist', $world);
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . '§aThe Arena was saved');
    }

    public function getRandomSpawnOitc(): array
    {
        // random array
        $spawns = [
            [
                'x' => 246,
                'y' => 67,
                'z' => 180
            ],
            [
                'x' => 187,
                'y' => 65,
                'z' => 180
            ],
            [
                'x' => 260,
                'y' => 65,
                'z' => 271
            ]
        ];
        $random = array_rand($spawns);
        return $spawns[$random];
    }

    public function getRandomSpawnBuild(): array
    {
        // random array
        $spawns = [
            [
                'x' => 263,
                'y' => 80,
                'z' => 269
            ],
            [
                'x' => 238,
                'y' => 81,
                'z' => 249
            ],
            [
                'x' => 219,
                'y' => 79,
                'z' => 287
            ]
        ];
        $random = array_rand($spawns);
        return $spawns[$random];
    }

    /**
     * @throws JsonException
     */

    public function setOITCArena(Player $player, string $world): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('OITC', $world);
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @throws JsonException
     */

    public function setSkywarsArena(Player $player, string $world): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Skywars', $world);
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @throws JsonException
     */

    public function setBotArena(Player $player, string $world): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Bot', $world);
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @throws JsonException
     */

    public function setBuildArena(Player $player, string $world): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Build', $world);
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @throws JsonException
     */
    public function setResistanceArena(Player $player, string $world): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Resistance', $world);
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @throws JsonException
     */
    public function setSumoD(Player $player, string $world): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('SumoD', $world);
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @throws JsonException
     */

    public function setKitPVPArena(Player $player, string $world): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('KitPVP', $world);
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @throws JsonException
     */

    public function setBoxingArena(Player $player, string $world): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Boxing', $world);
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @throws JsonException
     */
    public function setComboArena(Player $player, string $world): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Combo', $world);
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @throws JsonException
     */
    public function setKnockbackArena(Player $player, string $world): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Knockback', $world);
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @throws JsonException
     */
    public function removeOITC(Player $player): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('OITC');
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . 'Removed arena');
    }

    /**
     * @throws JsonException
     */
    public function removeBuild(Player $player): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Build');
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . 'Removed arena');
    }

    /**
     * @throws JsonException
     */
    public function removeFist(Player $player): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Fist');
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . 'Removed arena');
    }

    /**
     * @throws JsonException
     */
    public function removeBot(Player $player): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Bot');
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . 'Removed arena');
    }

    /**
     * @throws JsonException
     */
    public function removeKnockback(Player $player): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Knockback');
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . 'Removed arena');
    }

    /**
     * @throws JsonException
     */
    public function removeSkywars(Player $player): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Skywars');
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . 'Removed arena');
    }

    /**
     * @throws JsonException
     */
    public function removeSumoD(Player $player): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('SumoD');
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . 'Removed arena');
    }

    /**
     * @throws JsonException
     */
    public function removeBoxing(Player $player): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Boxing');
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . 'Removed arena');
    }

    /**
     * @throws JsonException
     */
    public function removeResistance(Player $player): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Resistance');
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . 'Removed arena');
    }

    /**
     * @throws JsonException
     */
    public function removeCombo(Player $player): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Combo');
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . 'Removed arena');
    }

    /**
     * @throws JsonException
     */
    public function removeKitPVP(Player $player): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('KitPVP');
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . 'Removed arena');
    }
}