<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\entry;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class BlockPlaceEntry extends Entry {
    public function __construct(
        protected Block $block,
        protected int $delay = 100
    ){
        $this->id = Level::blockHash($this->block->getFloorX(), $this->block->getFloorY(), $this->block->getFloorZ());
    }

    public function getDelay(): int{
        return $this->delay;
    }

    public function handle(): void {
        $level = $this->block->getLevel();
        $level->setBlock($this->block, $this->block);
        $level->broadcastLevelSoundEvent($this->block, LevelSoundEventPacket::SOUND_POP);
    }
}