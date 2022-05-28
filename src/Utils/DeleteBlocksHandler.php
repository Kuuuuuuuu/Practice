<?php

declare(strict_types=1);

namespace Kuu\Utils;

use Kuu\ConfigCore;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\sound\BlockPunchSound;
use pocketmine\world\World;

class DeleteBlocksHandler
{

    private array $buildBlocks = [];

    public function setBlockBuild(Block $block, bool $break = false): void
    {
        $pos = $block->getPosition()->getX() . ':' . $block->getPosition()->getY() . ':' . $block->getPosition()->getZ() . ':' . $block->getPosition()->getWorld()->getFolderName();
        if ($break && isset($this->buildBlocks[$pos])) {
            unset($this->buildBlocks[$pos]);
            return;
        }
        $this->buildBlocks[$pos] = ConfigCore::DeleteBlockTime;
    }

    public function update(): void
    {
        foreach ($this->buildBlocks as $pos => $sec) {
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
                    unset($this->buildBlocks[$pos]);
                } else {
                    $this->buildBlocks[$pos]--;
                }
            }
        }
    }
}
