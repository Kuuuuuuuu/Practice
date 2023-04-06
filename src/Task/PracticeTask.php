<?php

declare(strict_types=1);

namespace Nayuki\Task;

use Nayuki\Duel\Duel;
use Nayuki\Misc\AbstractTask;
use Nayuki\PracticeCore;
use pocketmine\utils\TextFormat;

class PracticeTask extends AbstractTask
{
    /**
     * @param int $tick
     * @return void
     */
    public function onUpdate(int $tick): void
    {
        foreach (PracticeCore::getDuelManager()->getArenas() as $duel) {
            if ($duel instanceof Duel) {
                $duel->update($tick);
            }
        }
        foreach (PracticeCore::getSessionManager()->getSessions() as $session) {
            $player = $session->getPlayer();
            if ($session->loadedData && $player->isConnected()) {
                if ($tick % 5 === 0) {
                    $session->updateScoreTag();
                }
                if ($tick % 20 === 0) {
                    $session->updateNameTag();
                    $session->updateScoreboard();
                    if ($session->isCombat()) {
                        $session->CombatTime--;
                        if ($session->CombatTime <= 0) {
                            $session->setCombat(false);
                            $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'You are no longer in combat.');
                            $session->BoxingPoint = 0;
                            $session->setOpponent(null);
                        }
                    }
                }
            } elseif (!$session->loadedData && !$player->isOnline()) {
                PracticeCore::getSessionManager()->removeSession($player);
            }
        }
    }
}
