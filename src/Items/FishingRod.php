<?php


namespace Kohaku\Items;


use Kohaku\Entity\FishingHook;
use Kohaku\NeptunePlayer;
use pocketmine\entity\Location;
use pocketmine\item\FishingRod as FishingRodItem;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\ThrowSound;

class FishingRod extends FishingRodItem
{

    public function __construct(ItemIdentifier $id, string $name)
    {
        parent::__construct($id, $name);
    }

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult
    {
        if (!$player->hasItemCooldown($this)) {
            $player->resetItemCooldown($this);
            /* @var NeptunePlayer $player */
            $location = $player->getLocation();
            new FishingHook(Location::fromObject($player->getEyePos(), $player->getWorld(), $location->yaw, $location->pitch), $player, null);
            $location->getWorld()->addSound($location, new ThrowSound());
            return ItemUseResult::SUCCESS();
        }
        return ItemUseResult::FAIL();
    }
}