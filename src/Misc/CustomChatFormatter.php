<?php

namespace Nayuki\Misc;

use Nayuki\PracticeCore;
use pocketmine\lang\Translatable;
use pocketmine\player\chat\ChatFormatter;

final class CustomChatFormatter implements ChatFormatter
{
    public function format(string $username, string $message): Translatable|string
    {
        foreach (PracticeCore::getSessionManager()->getSessions() as $sessions) {
            $player = $sessions->getPlayer();
            if ($player->getName() === $username) {
                $customTag = $sessions->getCustomTag();
                $NameTag = '§a' . $player->getDisplayName() . '§r§a > §r' . $message;
                if ($customTag !== '') {
                    $NameTag = "$customTag §b" . $NameTag;
                }
                return $NameTag;
            }
        }
        return '§a' . $username . '§r§a > §r' . $message;
    }
}
