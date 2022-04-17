<?php

declare(strict_types=1);

namespace Kohaku\Utils;

use Kohaku\Loader;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\sound\BlockPunchSound;

class DeleteBlocksHandler
{

    private array $buildBlocks = [];

    public function setBlockBuild(Block $block, bool $break = false): void
    {
        $pos = $block->getPosition()->getX() . ':' . $block->getPosition()->getY() . ':' . $block->getPosition()->getZ() . ':' . $block->getPosition()->getWorld()->getFolderName();
        if ($break and isset($this->buildBlocks[$pos])) {
            unset($this->buildBlocks[$pos]);
        } else {
            $this->buildBlocks[$pos] = Loader::getInstance()->DeleteBlockTime;
        }
    }

    public function update(): void
    {
        if (count($this->buildBlocks) > 0) {
            foreach ($this->buildBlocks as $pos => $sec) {
                $block = explode(':', $pos);
                $x = (int)$block[0];
                $y = (int)$block[1];
                $z = (int)$block[2];
                $level = Server::getInstance()->getWorldManager()->getWorldByName($block[3]);
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
