<?php

namespace Kohaku\Utils;

use Kohaku\Entity\FistBot;
use pocketmine\player\Player;

class BotUtils
{

    public function spawnFistBot(Player $player, bool $spawn): void
    {
        if ($spawn) {
            $npc = new FistBot($player->getLocation(), $player->getSkin(), null, $player->getName());
            $npc->spawnToAll();
        }
    }
}