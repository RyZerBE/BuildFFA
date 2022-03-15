<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\entry;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\level\Level;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

class BlockBreakEntry extends Entry {
    public function __construct(
        protected Block $block,
        protected int $delay = 100
    ){
        $this->block->getLevel()->broadcastLevelEvent($this->block, LevelEventPacket::EVENT_BLOCK_START_BREAK, (int) (65535 / $this->getDelay()));
        $this->id = Level::blockHash($this->block->getFloorX(), $this->block->getFloorY(), $this->block->getFloorZ());
    }

    public function getDelay(): int{
        return $this->delay;
    }

    public function handle(): void {
        $level = $this->block->getLevel();
        $level->addParticle(new DestroyBlockParticle($this->block, $this->block));
        $level->setBlock($this->block, BlockFactory::get(0));
    }
}