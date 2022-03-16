<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\block;

use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\Listener;

class BlockBurnListener implements Listener {
    public function onBlockBurn(BlockBurnEvent $event): void {
        $event->setCancelled();
    }
}