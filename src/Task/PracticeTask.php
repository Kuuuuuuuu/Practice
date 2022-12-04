<?php

declare(strict_types=1);

namespace Kuu\Task;

use Kuu\Misc\AbstractTask;
use Kuu\Players\PlayerSession;
use Kuu\PracticeConfig;
use Kuu\PracticeCore;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\player\Player;
use pocketmine\Server;

class PracticeTask extends AbstractTask
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $tick
     * @return void
     */
    public function onUpdate(int $tick): void
    {
        if ($tick % 20 === 0) {
            foreach (PracticeCore::getPracticeUtils()->getPlayerInSession() as $player) {
                $session = PracticeCore::getPlayerSession()::getSession($player);
                $this->updateNameTag($player, $session);
                $this->updateScoreboard($player);
                $this->updateScoreTag($player);
                if ($session->isCombat()) {
                    $session->CombatTime--;
                    if ($session->CombatTime <= 0) {
                        $session->setCombat(false);
                        $player->getXpManager()->setXpProgress(0.0);
                        $player->sendMessage(PracticeCore::getPrefixCore() . '§cYou are no longer in combat.');
                        $session->BoxingPoint = 0;
                        $session->setOpponent(null);
                    }
                }
            }
        }
    }

    /**
     * @param Player $player
     * @param PlayerSession $session
     * @return void
     */
    private function updateNameTag(Player $player, PlayerSession $session): void
    {
        if ($session->getCustomTag() !== '') {
            $Tag = '§f[' . $session->getCustomTag() . '§f] §b' . $player->getDisplayName();
        } else {
            $Tag = '§b' . $player->getDisplayName();
        }
        $player->setNameTag($Tag);
    }

    /**
     * @param Player $player
     * @return void
     */
    private function updateScoreboard(Player $player): void
    {
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
            PracticeCore::getInstance()->getScoreboardManager()->setLobbyScoreboard($player);
        } elseif ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBoxingArena())) {
            PracticeCore::getInstance()->getScoreboardManager()->setBoxingScoreboard($player);
        } else {
            PracticeCore::getInstance()->getScoreboardManager()->setArenaScoreboard($player);
        }
    }

    /**
     * @param Player $player
     * @return void
     */
    private function updateScoreTag(Player $player): void
    {
        $ping = $player->getNetworkSession()->getPing();
        $cps = PracticeCore::getClickHandler()->getClicks($player);
        $player->sendData($player->getViewers(), [EntityMetadataProperties::SCORE_TAG => new StringMetadataProperty(PracticeConfig::COLOR . $ping . ' §fMS §f| ' . PracticeConfig::COLOR . $cps . ' §fCPS')]);
    }
}
