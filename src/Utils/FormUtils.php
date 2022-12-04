<?php

declare(strict_types=1);

namespace Kuu\Utils;

use Kuu\PracticeConfig;
use Kuu\PracticeCore;
use Kuu\Utils\Forms\SimpleForm;
use pocketmine\player\Player;

class FormUtils
{
    /**
     * @param Player $player
     * @return void
     */
    public function Form1(Player $player): void
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            if ($data !== null) {
                switch ($data) {
                    case 0:
                        PracticeCore::getArenaManager()->onJoinBoxing($player);
                        break;
                    case 1:
                        PracticeCore::getArenaManager()->onJoinNodebuff($player);
                        break;
                    default:
                        print 'Error';
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cMenu');
        $form->addButton("§aBoxing\n§bPlayers: §f" . PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getBoxingArena()), 0, 'textures/items/diamond_sword.png');
        $form->addButton("§aNodebuff\n§bPlayers: §f" . PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getNodebuffArena()), 0, 'textures/items/potion_bottle_splash_heal.png');
        $player->sendForm($form);
    }
}
