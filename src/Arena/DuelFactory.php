<?php

declare(strict_types=1);

namespace Kuu\Arena;

use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use Kuu\Utils\Kits\KitManager;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Location;
use pocketmine\player\GameMode;
use pocketmine\Server;
use pocketmine\world\World;

class DuelFactory extends DuelFactoryBase
{
    private int $time = 903;
    private PracticePlayer $player1;
    private PracticePlayer $player2;
    private ?PracticePlayer $winner = null;
    private ?PracticePlayer $loser = null;
    private World $level;
    private KitManager $kit;
    private int $phase = 0;

    public function __construct(string $name, PracticePlayer $player1, PracticePlayer $player2, KitManager $kit)
    {
        $world = $this->Load($name, $this);
        $this->level = $world;
        $this->kit = $kit;
        $this->player1 = $player1;
        $this->player2 = $player2;
    }

    public function update(): void
    {
        foreach ([$this->player1, $this->player2] as $player) {
            /* @var PracticePlayer $player */
            if ($player->isOnline()) {
                if (($player->getPosition()->getY() < 100) && $player->getWorld() === $this->level) {
                    $player->kill();
                }
                if (!$player->isDueling()) {
                    $this->loser = $player;
                    $this->winner = $player->getName() !== $this->player1->getName() ? $this->player1 : $this->player2;
                    $this->onEnd();
                }
            } else {
                $this->loser = $player;
                $this->winner = $player->getName() !== $this->player1->getName() ? $this->player1 : $this->player2;
                $this->onEnd($player);
            }
        }
        if ($this->phase !== self::ENDED) {
            if ($this->time === 903) {
                foreach ([$this->player1, $this->player2] as $player) {
                    /* @var PracticePlayer $player */
                    $player->setGamemode(GameMode::SURVIVAL());
                    $player->getArmorInventory()->setContents($this->kit->getArmorItems());
                    $player->getInventory()->setContents($this->kit->getInventoryItems());
                    $player->setImmobile();
                    $player->sendTitle('§d3', '', 1, 3, 1);
                    PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $player);
                    if ($this->kit->getName() === 'Sumo') {
                        $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 1000, 255, false));
                    }
                }
                if ($this->kit->getName() === 'Sumo') {
                    $this->player1->teleport(new Location(6, 101, 0, $this->level, 0, 0));
                    $this->player2->teleport(new Location(6, 101, 9, $this->level, 180, 0));
                } else {
                    $this->player1->teleport(new Location(24, 101, 40, $this->level, 180, 0));
                    $this->player2->teleport(new Location(24, 101, 10, $this->level, 0, 0));
                }
            } elseif ($this->time === 902) {
                foreach ([$this->player1, $this->player2] as $player) {
                    /* @var PracticePlayer $player */
                    $player->setCurrentKit(null);
                    $player->sendTitle('§d2', '', 1, 3, 1);
                    PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $player);
                }
            } elseif ($this->time === 901) {
                foreach ([$this->player1, $this->player2] as $player) {
                    /* @var PracticePlayer $player */
                    $player->sendTitle('§d1', '', 1, 3, 1);
                    PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $player);
                }
            } elseif ($this->time === 900) {
                $this->phase = self::INGAME;
                foreach ([$this->player1, $this->player2] as $player) {
                    /* @var PracticePlayer $player */
                    $player->sendTitle('§dFight!', '', 1, 3, 1);
                    PracticeCore::getInstance()->getPracticeUtils()->playSound('random.anvil_use', $player);
                    $player->setImmobile(false);
                }
            } elseif ($this->time <= 1) {
                $this->onEnd();
            }
        }
        $this->time--;
    }

    public function onEnd(?PracticePlayer $playerLeft = null): void
    {
        if ($this->phase === self::INGAME) {
            foreach ([$this->player1, $this->player2] as $online) {
                /* @var PracticePlayer $online */
                if (is_null($playerLeft) || $online->getName() !== $playerLeft->getName()) {
                    if ($online instanceof PracticePlayer) {
                        $online->sendMessage('§f-----------------------');
                        $winnerMessage = '§aWinner: §f';
                        $winnerMessage .= ($this->winner !== null) ? $this->winner->getName() : 'None';
                        $online->sendMessage($winnerMessage);
                        $loserMessage = '§cLoser: §f';
                        $loserMessage .= ($this->loser !== null) ? $this->loser->getName() : 'None';
                        $online->sendMessage($loserMessage);
                        $online->sendMessage('§f-----------------------');
                        PracticeCore::getPracticeUtils()->GiveLobbyItem($online);
                        PracticeCore::getScoreboardManager()->sb($online);
                        $online->setDueling(false);
                        $online->setCurrentKit(null);
                        $online->setHealth(20);
                        $online->setGamemode(GameMode::ADVENTURE());
                        $online->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()?->getSafeSpawn(), 0, 0);
                    }
                }
            }
            $this->phase = self::ENDED;
            PracticeCore::getDuelManager()->stopMatch($this->level->getFolderName());
        }
    }
}