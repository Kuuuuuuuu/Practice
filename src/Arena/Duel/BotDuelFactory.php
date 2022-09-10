<?php

declare(strict_types=1);

namespace Kuu\Arena\Duel;

use Kuu\Entity\PracticeBot;
use Kuu\PracticeConfig;
use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use Kuu\Utils\Kits\KitManager;
use pocketmine\entity\Location;
use pocketmine\player\GameMode;
use pocketmine\Server;
use pocketmine\world\World;

class BotDuelFactory extends DuelFactoryBase
{
    private static int $time;
    private PracticePlayer $player1;
    private ?PracticeBot $player2;
    private World $level;
    private KitManager $kit;
    private string $mode;
    private int $phase = 0;

    public function __construct(string $name, PracticePlayer $player1, KitManager $kit, string $mode)
    {
        $world = self::Load($name, $this);
        $this->level = $world;
        $this->player1 = $player1;
        $this->player2 = null;
        $this->kit = $kit;
        $this->mode = $mode;
        self::$time = self::DEFAULT_TIME;
    }

    public function update(): void
    {
        if ($this->player1->isOnline()) {
            if ($this->phase !== self::ENDED) {
                if (!$this->player1->isDueling()) {
                    $this->onEnd();
                }
                if ($this->player2 instanceof PracticeBot) {
                    if ($this->player2?->pearlcooldown !== 0) {
                        $this->player2->pearlcooldown--;
                    }
                    if (!$this->player2?->isAlive() || $this->player2?->isClosed()) {
                        $this->onEnd($this->player1);
                    }
                }
                if (self::$time === 903) {
                    $this->player1->setImmobile();
                    $this->player1->setGamemode(GameMode::SURVIVAL());
                    $this->player1->sendTitle('§d3', '', 1, 3, 1);
                    $this->player1->getArmorInventory()->setContents($this->kit->getArmorItems());
                    $this->player1->getInventory()->setContents($this->kit->getInventoryItems());
                    $this->player1->teleport(new Location(24, 101, 40, $this->level, 190, 0));
                    PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $this->player1);
                } elseif (self::$time === 902) {
                    $this->player1->setCurrentKit(null);
                    $this->player1->sendTitle('§d2', '', 1, 3, 1);
                    PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $this->player1);
                } elseif (self::$time === 901) {
                    $this->player1->sendTitle('§d1', '', 1, 3, 1);
                    PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $this->player1);
                } elseif (self::$time === 900) {
                    $this->phase = self::INGAME;
                    $this->player1->sendTitle('§dFight!', '', 1, 3, 1);
                    PracticeCore::getInstance()->getPracticeUtils()->playSound('random.anvil_use', $this->player1);
                    $this->player1->setImmobile(false);
                    $this->player2 = new PracticeBot(new Location(24, 101, 10, Server::getInstance()->getWorldManager()->getWorldByName($this->level->getFolderName()), 0, 0), $this->player1->getSkin(), null, $this->player1->getName(), $this->mode);
                } elseif (self::$time <= 1) {
                    $this->onEnd();
                }
            }
            self::$time--;
        } else {
            $this->onEnd();
        }
    }

    public function onEnd(?PracticePlayer $playerLeft = null): void
    {
        if ($this->phase !== self::ENDED) {
            if ($playerLeft instanceof PracticePlayer) {
                $winnerMessage = '§aWinner: §f' . ($this->player1->getName() ?? 'None');
                $loserMessage = '§cLoser: §f' . PracticeConfig::BOTNAME;
            } else {
                $winnerMessage = '§aWinner: §f' . PracticeConfig::BOTNAME;
                $loserMessage = '§cLoser: §f' . ($this->player1->getName() ?? 'None');
            }
            if ($this->player1->isOnline()) {
                $this->player1->sendMessage('§f-----------------------');
                $this->player1->sendMessage($winnerMessage);
                $this->player1->sendMessage($loserMessage);
                $this->player1->sendMessage('§f-----------------------');
                $this->player1->setDueling(false);
                $this->player1->setCurrentKit(null);
                $this->player1->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()?->getSafeSpawn(), 0, 0);
                PracticeCore::getPracticeUtils()->GiveLobbyItem($this->player1);
                PracticeCore::getScoreboardManager()->sb($this->player1);
                $this->player1->setHealth(20);
            }
        }
        $this->phase = self::ENDED;
        PracticeCore::getDuelManager()->stopMatch($this->level->getFolderName());
    }
}