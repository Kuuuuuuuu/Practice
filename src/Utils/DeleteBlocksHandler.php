<?php

declare(strict_types=1);

namespace Kohaku\Utils;

use Kohaku\Loader;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\Server;
use pocketmine\world\particle\BlockPunchParticle;
use pocketmine\world\sound\BlockPunchSound;

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
            $block = explode(':', $pos);
            $x = (int)$block[0];
            $y = (int)$block[1];
            $z = (int)$block[2];
            $level = Server::getInstance()->getWorldManager()->getWorldByName($block[3]);
            $block = $level->getBlockAt($x, $y, $z);
            $blockvec = new Vector3($x, $y, $z);
            if ($sec <= 0) {
                $level->addParticle($blockvec, new BlockPunchParticle($block, RuntimeBlockMapping::getInstance()->toRuntimeId($block->getFullId())));
                $level->addSound($blockvec, new BlockPunchSound($block));
                $level->setBlock($blockvec, BlockFactory::getInstance()->get(BlockLegacyIds::AIR, 0), true);
                unset($this->buildBlocks[$pos]);
            } else {
                $this->buildBlocks[$pos]--;
            }
        }
    }
}
