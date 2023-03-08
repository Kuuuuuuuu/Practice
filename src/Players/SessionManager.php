<?php

declare(strict_types=1);

namespace Nayuki\Players;

use pocketmine\player\Player;

use function strlen;

final class SessionManager
{
    /** @var PlayerSession[] */
    public array $session = [];

    /**
     * @param Player $player
     * @return PlayerSession
     */
    public function getSession(Player $player): PlayerSession
    {
        return $this->session[spl_object_hash($player)] ??= new PlayerSession($player);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function removeSession(Player $player): void
    {
        unset($this->session[spl_object_hash($player)]);
    }

    /**
     * @return PlayerSession[]
     */
    public function getSessions(): array
    {
        return $this->session;
    }

    /**
     * @param string $name
     * @return Player|null
     */
    public function getPlayerInSessionByPrefix(string $name): ?Player
    {
        $found = null;
        $name = strtolower($name);
        $delta = PHP_INT_MAX;
        foreach ($this->session as $session) {
            $player = $session->getPlayer();
            if (stripos($player->getName(), $name) === 0) {
                $curDelta = strlen($player->getName()) - strlen($name);
                if ($curDelta < $delta) {
                    $found = $player;
                    $delta = $curDelta;
                }
                if ($curDelta === 0) {
                    break;
                }
            }
        }
        return $found;
    }
}
