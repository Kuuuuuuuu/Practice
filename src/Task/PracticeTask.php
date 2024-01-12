<?php

declare(strict_types=1);

namespace Nayuki\Task;

use Nayuki\Misc\AbstractTask;
use Nayuki\PracticeCore;
use pocketmine\utils\TextFormat;

final class PracticeTask extends AbstractTask
{
    /**
     * @param int $tick
     * @return void
     */
    protected function onUpdate(int $tick): void
    {
        $sessionManager = PracticeCore::getSessionManager();
        $duelManager = PracticeCore::getDuelManager();

        foreach ($duelManager->getArenas() as $duel) {
            if ($tick % 20 === 0) {
                $duel->update();
            }
        }

        foreach ($sessionManager->getSessions() as $session) {
            $player = $session->getPlayer();

            if (!$session->loadedData && (!$player->isConnected() || !$player->isOnline())) {
                $sessionManager->removeSession($player);
                return;
            }

            if ($tick % 5 === 0) {
                $session->updateScoreTag();
                $session->updateScoreboard();

                if ($tick % 20 === 0) {
                    $session->updateNameTag();
                    $isCombat = $session->isCombat;

                    if ($isCombat) {
                        $combatTime = --$session->CombatTime;

                        if ($combatTime <= 0) {
                            $session->isCombat = false;
                            $session->CombatTime = 0;
                            $session->BoxingPoint = 0;
                            $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'You are no longer in combat.');
                            $session->setOpponent(null);
                        }
                    }
                }
            }
        }
    }
}
