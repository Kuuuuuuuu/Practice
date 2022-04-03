<?php

declare(strict_types=1);

namespace Kohaku\Core\Utils;

use Kohaku\Core\Loader;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
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
                $x = (int)$block[0];
                $y = (int)$block[1];
                $z = (int)$block[2];
                $level = Server::getInstance()->getWorldManager()->getWorldByName($block[3]);
                $level->broadcastPacketToViewers(new Vector3($x, $y, $z), LevelEventPacket::create(LevelEvent::BLOCK_START_BREAK, (int)(65535 * 2), new Vector3($x, $y, $z)));
                $level->setBlock(new Vector3($x, $y, $z), BlockFactory::getInstance()->get(BlockLegacyIds::AIR, 0), true);
                unset($this->buildBlocks[$pos]);
            } else {
                $this->buildBlocks[$pos]--;
            }
        }
    }
}
