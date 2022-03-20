<?php

declare(strict_types=1);

namespace Kohaku\Core\Utils;

use JetBrains\PhpStorm\Pure;
use Kohaku\Core\Loader;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\math\Vector3;
use pocketmine\Server;

class DeleteBlocksHandler
{

    #[Pure] public static function getInstance(): DeleteBlocksHandler
    {
        return new DeleteBlocksHandler();
    }

    public function setBlockBuild(Block $block, bool $break = false): void
    {
        $pos = $block->getPosition()->getX() . ':' . $block->getPosition()->getY() . ':' . $block->getPosition()->getZ() . ':' . $block->getPosition()->getWorld()->getFolderName();
        if ($break && isset(Loader::getInstance()->buildBlocks[$pos])) {
            unset(Loader::getInstance()->buildBlocks[$pos]);
        } else {
            Loader::getInstance()->buildBlocks[$pos] = 10;
        }
    }

    public function update(): void
    {
        foreach (Loader::getInstance()->buildBlocks as $pos => $sec) {
            if ($sec <= 0) {
                $block = explode(':', $pos);
                $x = $block[0];
                $y = $block[1];
                $z = $block[2];
                $level = Server::getInstance()->getWorldManager()->getWorldByName($block[3]);
                $level->setBlock(new Vector3((int)$x, (int)$y, (int)$z), BlockFactory::getInstance()->get(BlockLegacyIds::AIR, 0));
                unset(Loader::getInstance()->buildBlocks[$pos]);
            } else {
                Loader::getInstance()->buildBlocks[$pos]--;
            }
        }
    }
}
