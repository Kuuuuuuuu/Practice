<?php

namespace Nayuki\Items;

use Nayuki\Entity\EnderPearlEntity;
use Nayuki\PracticeConfig;
use Nayuki\PracticeCore;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\EnderPearl as PMEnderPearlProjectile;
use pocketmine\entity\projectile\Throwable;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\item\EnderPearl as ItemEnderPearl;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\ThrowSound;

class EnderPearl extends ItemEnderPearl
{
    public function __construct(ItemIdentifier $id, string $name)
    {
        parent::__construct($id, $name);
    }

    /**
     * @param Player $player
     * @param Vector3 $directionVector
     * @return ItemUseResult
     */
    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult
    {
        $location = $player->getLocation();
        $projectile = $this->createEntity(Location::fromObject($player->getEyePos(), $player->getWorld(), $location->yaw, $location->pitch), $player);
        $projectile->setMotion($directionVector->multiply($this->getThrowForce()));
        $projectileEv = new ProjectileLaunchEvent($projectile);
        $projectileEv->call();
        if ($projectileEv->isCancelled()) {
            $projectile->flagForDespawn();
            return ItemUseResult::FAIL();
        }
        $projectile->spawnToAll();
        $location->getWorld()->addSound($location, new ThrowSound());
        $this->pop();
        return ItemUseResult::SUCCESS();
    }

    /**
     * @param Location $location
     * @param Player $thrower
     * @return Throwable
     */
    public function createEntity(Location $location, Player $thrower): Throwable
    {
        $session = PracticeCore::getSessionManager()::getSession($thrower);
        if ($session->SmoothPearlEnabled) {
            return new EnderPearlEntity($location, $thrower);
        }
        return new PMEnderPearlProjectile($location, $thrower);
    }

    /**
     * @return float
     */
    public function getThrowForce(): float
    {
        return PracticeConfig::PearlForce;
    }
}
