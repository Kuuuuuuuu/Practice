<?php

declare(strict_types=1);

namespace Kuu\Utils;

use Kuu\PracticeConfig;
use Kuu\PracticeCore;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\sound\BlockPunchSound;
use pocketmine\world\World;

class DeleteBlocksHandler
{
    public function setBlockBuild(Block $block, bool $break = false): void
    {
        $pos = $block->getPosition()->getX() . ':' . $block->getPosition()->getY() . ':' . $block->getPosition()->getZ() . ':' . $block->getPosition()->getWorld()->getFolderName();
        if ($break && isset(PracticeCore::getCaches()->buildBlocks[$pos])) {
            unset(PracticeCore::getCaches()->buildBlocks[$pos]);
        } else {
            PracticeCore::getCaches()->buildBlocks[$pos] = PracticeConfig::DeleteBlockTime;
        }
    }

    public function update(): void
    {
        foreach (PracticeCore::getCaches()->buildBlocks as $pos => $sec) {
            $block = explode(':', $pos);
            $level = Server::getInstance()->getWorldManager()->getWorldByName($block[3]);
            if ($level instanceof World) {
                $x = (int)$block[0];
                $y = (int)$block[1];
                $z = (int)$block[2];
                $block = $level->getBlockAt($x, $y, $z);
                $blockvec = new Vector3($x, $y, $z);
                if ($sec <= 0) {
                    $level->addSound($blockvec, new BlockPunchSound($block));
                    $level->setBlock($blockvec, VanillaBlocks::AIR());
                    unset(PracticeCore::getCaches()->buildBlocks[$pos]);
                } else {
                    PracticeCore::getCaches()->buildBlocks[$pos]--;
                }
            }
        }
    }
}
