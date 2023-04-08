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
                    $session->updateScoreboard();
                }
                if ($tick % 20 === 0) {
                    $session->updateNameTag();
                    if ($session->isCombat) {
                        $session->CombatTime--;
                        if ($session->CombatTime <= 0) {
                            $session->isCombat = false;
                            $session->CombatTime = 0;
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
