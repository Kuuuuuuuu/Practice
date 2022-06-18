<?php

namespace Kuu\Items;

use JetBrains\PhpStorm\Pure;
use Kuu\ConfigCore;
use Kuu\Entity\EnderPearlEntity;
use Kuu\NeptunePlayer;
use pocketmine\entity\Location;
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

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult
    {
        //TODO: Implement this
        if (($player instanceof NeptunePlayer) && !$player->isEnderPearlCooldown()) {
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
        return ItemUseResult::FAIL();
    }

    protected function createEntity(Location $location, Player $thrower): Throwable
    {
        return new EnderPearlEntity($location, $thrower);
    }

    #[Pure] public function getThrowForce(): float
    {
        return ConfigCore::PearlForce;
    }
}