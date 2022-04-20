<?php

namespace Kohaku\Utils;

use JsonException;
use Kohaku\Loader;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as Color;

class KnockbackManager
{

    /**
     * @throws JsonException
     */
    public function setKnockback(Player $player, string $world, float|int $knockback1, float|int $knockback2): void
    {
        if (!file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $world)) {
            $player->sendMessage(Loader::getPrefixCore() . Color::RED . 'World ' . $world . ' not found');
        } else {
            $data = new Config(Loader::getInstance()->getDataFolder() . 'data/kb.yml', Config::YAML);
            $data->set($world, ['hkb' => $knockback1, 'ykb' => $knockback2]);
            $data->save();
            $player->sendMessage(Loader::getPrefixCore() . Color::GREEN . 'Knockback set to ' . $knockback1 . ' for world ' . $world);
        }
    }

    /**
     * @throws JsonException
     */
    public function setAttackspeed(Player $player, string $world, int $speed): void
    {
        if (!file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $world)) {
            $player->sendMessage(Loader::getPrefixCore() . Color::RED . 'World ' . $world . ' not found');
        } else {
            $data = new Config(Loader::getInstance()->getDataFolder() . 'data/speed.yml', Config::YAML);
            $data->set($world, $speed);
            $data->save();
            $player->sendMessage(Loader::getPrefixCore() . Color::GREEN . 'Attackspeed set to ' . $speed . ' for world ' . $world);
        }
    }

    public function getKnockback(string $world): ?array
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/kb.yml', Config::YAML);
        return $data->get($world) ?? null;
    }

    public function getAttackspeed(string $world): ?int
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/speed.yml', Config::YAML);
        return $data->get($world) ?? null;
    }

    /**
     * @throws JsonException
     */
    public function removeAttackspeed(Player $player, string $world): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/speed.yml', Config::YAML);
        $data->remove($world);
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . Color::GREEN . 'Attackspeed removed for world ' . $world);
    }

    /**
     * @throws JsonException
     */
    public function removeKnockback(Player $player, string $world): void
    {
        $data = new Config(Loader::getInstance()->getDataFolder() . 'data/kb.yml', Config::YAML);
        $data->remove($world);
        $data->save();
        $player->sendMessage(Loader::getPrefixCore() . Color::GREEN . 'Knockback removed for world ' . $world);
    }
}