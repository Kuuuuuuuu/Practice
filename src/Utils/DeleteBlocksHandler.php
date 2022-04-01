<?php

declare(strict_types=1);

namespace Kohaku\Core\Utils;

use Kohaku\Core\Loader;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\math\Vector3;
use pocketmine\Server;

class DeleteBlocksHandler
{

    private array $buildBlocks = [];

    public function setBlockBuild(Block $block, bool $break = false): void
    {
        $pos = $block->getPosition()->getX() . ':' . $block->getPosition()->getY() . ':' . $block->getPosition()->getZ() . ':' . $block->getPosition()->getWorld()->getFolderName();
        if ($break && isset($this->buildBlocks[$pos])) {
            unset($this->buildBlocks[$pos]);
        } else {
            $this->buildBlocks[$pos] = Loader::getInstance()->DeleteBlockTime;
        }
    }

    public function update(): void
    {
        if (count($this->buildBlocks) === 0) {
            return;
        }
        foreach ($this->buildBlocks as $pos => $sec) {
            if ($sec <= 0) {
                $block = explode(':', $pos);
                $x = $block[0];
                $y = $block[1];
                $z = $block[2];
                $level = Server::getInstance()->getWorldManager()->getWorldByName($block[3]);
                $level->setBlock(new Vector3((int)$x, (int)$y, (int)$z), BlockFactory::getInstance()->get(BlockLegacyIds::AIR, 0), true);
                unset($this->buildBlocks[$pos]);
            } else {
                $this->buildBlocks[$pos]--;
            }
        }
    }
}
