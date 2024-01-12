<?php

namespace Nayuki\Misc;

use Nayuki\PracticeCore;
use pocketmine\lang\Translatable;
use pocketmine\player\chat\ChatFormatter;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

final class CustomChatFormatter implements ChatFormatter
{
    public function format(string $username, string $message): Translatable|string
    {
        $onlinePlayers = Server::getInstance()->getOnlinePlayers();

        foreach ($onlinePlayers as $player) {
            $sessions = PracticeCore::getSessionManager()->getSession($player);

            if ($player->getName() === $username) {
                $customTag = $sessions->getCustomTag();
                $NameTag = TextFormat::GREEN . $player->getDisplayName() . '§r§a > §r' . $message;

                if ($customTag !== '') {
                    $NameTag = "$customTag " . TextFormat::AQUA . $NameTag;
                }

                return $NameTag;
            }
        }

        return TextFormat::GREEN . $username . '§r§a > §r' . $message;
    }
}
