<?php /** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kohaku\Core\Events;

use Exception;
use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ArenaUtils;
use pocketmine\event\entity\{EntityDamageEvent};
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\particle\HeartParticle;

class InterruptEvent implements Listener
{

    /**
     * @throws Exception
     */
    public function onDamage(EntityDamageEvent $event)
    {
        $player = $event->getEntity();
        if ($event->getCause() === EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
            $damager = $event->getDamager();
            if ($player instanceof Player and $damager instanceof Player) {
                if ($damager->getGamemode() === 1 or $player->getGamemode() === 1) return;
                if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKnockbackArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getOITCArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName("aqua")) return;
                if (!isset(Loader::getInstance()->opponent[$player->getName()]) and !isset(Loader::getInstance()->opponent[$damager->getName()])) {
                    Loader::getInstance()->opponent[$player->getName()] = $damager->getName();
                    Loader::getInstance()->opponent[$damager->getName()] = $player->getName();
                    Loader::getInstance()->CombatTimer[$player->getName()] = 10;
                    Loader::getInstance()->CombatTimer[$damager->getName()] = 10;
                    $player->sendMessage(Loader::getInstance()->message["StartCombat"]);
                    $damager->sendMessage(Loader::getInstance()->message["StartCombat"]);
                } else {
                    if (isset(Loader::getInstance()->opponent[$damager->getName()]) and isset(Loader::getInstance()->opponent[$player->getName()])) {
                        if (Loader::getInstance()->opponent[$player->getName()] === $damager->getName() and Loader::getInstance()->opponent[$damager->getName()] === $player->getName()) {
                            Loader::getInstance()->CombatTimer[$player->getName()] = 10;
                            Loader::getInstance()->CombatTimer[$damager->getName()] = 10;
                            if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBoxingArena())) {
                                if (isset(Loader::getInstance()->BoxingPoint[$damager->getName()])) {
                                    Loader::getInstance()->BoxingPoint[$damager->getName()] += 1;
                                    if (Loader::getInstance()->BoxingPoint[$damager->getName()] === 100) {
                                        $pos = $player->getPosition();
                                        $world = $player->getWorld();
                                        $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
                                        $world->addParticle($pos, new HeartParticle(3));
                                        ArenaUtils::getInstance()->DeathReset($player, $damager, "Boxing");
                                        foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $p) {
                                            $p->sendMessage(Loader::getPrefixCore() . "§a" . $player->getName() . " §fhas been killed by §c" . $player->getLastDamageCause()->getDamager()->getName());
                                        }
                                    }
                                } else {
                                    Loader::getInstance()->BoxingPoint[$damager->getName()] = 1;
                                }
                            }
                        }
                        return;
                    }
                    if (isset(Loader::getInstance()->opponent[$player->getName()]) and !isset(Loader::getInstance()->opponent[$damager->getName()])) {
                        $event->cancel();
                        $damager->sendMessage(Loader::getPrefixCore() . "§cDon't Interrupt!");
                        return;
                    }
                    if (!isset(Loader::getInstance()->opponent[$player->getName()]) and isset(Loader::getInstance()->opponent[$damager->getName()])) {
                        $event->cancel();
                        $damager->sendMessage(Loader::getPrefixCore() . "§cDon't Interrupt!");
                        return;
                    }
                    if (isset(Loader::getInstance()->opponent[$player->getName()]) and isset(Loader::getInstance()->opponent[$damager->getName()]) and Loader::getInstance()->opponent[$player->getName()] !== $damager->getName() and Loader::getInstance()->opponent[$damager->getName()] !== $player->getName()) {
                        $event->cancel();
                        $damager->sendMessage(Loader::getPrefixCore() . "§cYour Opponent is " . Loader::getInstance()->opponent[$damager->getName() ?? null] ?? null);
                    }
                }
            }
        }
    }
}
