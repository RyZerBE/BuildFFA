<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\block;

use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\Listener;

class BlockUpdateListener implements Listener {
    public function onBlockUpdate(BlockUpdateEvent $event): void {
        $event->setCancelled();
    }
}