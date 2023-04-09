<?php

declare(strict_types=1);

namespace Nayuki\Players;

use pocketmine\player\Player;

use function strlen;

final class SessionManager
{
    /** @var PlayerSession[] */
    public static array $session = [];

    /**
     * @param Player $player
     * @return PlayerSession
     */
    public function getSession(Player $player): PlayerSession
    {
        return self::$session[spl_object_hash($player)] ??= new PlayerSession($player);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function removeSession(Player $player): void
    {
        unset(self::$session[spl_object_hash($player)]);
    }

    /**
     * @return PlayerSession[]
     */
    public function getSessions(): array
    {
        return self::$session;
    }

    /**
     * @param string $name
     * @return Player|null
     */
    public function getPlayerInSessionByPrefix(string $name): ?Player
    {
        $name = strtolower($name);
        $found = null;
        $delta = PHP_INT_MAX;
        $nameLength = strlen($name);
        foreach (self::$session as $session) {
            $player = $session->getPlayer();
            $playerName = strtolower($player->getName());
            if (str_starts_with($playerName, $name)) {
                $curDelta = strlen($playerName) - $nameLength;
                if ($curDelta < $delta) {
                    $found = $player;
                    $delta = $curDelta;
                    if ($curDelta === 0) {
                        break;
                    }
                }
            }
        }
        return $found;
    }
}
