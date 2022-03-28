<?php

namespace Kohaku\Core\Utils;

use JetBrains\PhpStorm\Pure;
use Kohaku\Core\Entity\FistBot;
use pocketmine\player\Player;

class BotUtils
{

    #[Pure] public static function getInstance(): BotUtils
    {
        return new BotUtils();
    }

    public function spawnFistBot(Player $player, bool $spawn): void
    {
        if ($spawn) {
            $npc = new FistBot($player->getLocation(), $player->getSkin(), null, $player->getName());
            $npc->spawnToAll();
        }
    }
}