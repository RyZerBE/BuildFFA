<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\block;

use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\Listener;

class LeavesDecayListener implements Listener {
    public function onLeavesDecay(LeavesDecayEvent $event): void {
        $event->setCancelled();
    }
}