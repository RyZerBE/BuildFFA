<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\entity;

use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\Listener;
use ryzerbe\buildffa\game\entry\BlockPlaceEntry;
use ryzerbe\buildffa\game\GameManager;

class EntityExplodeListener implements Listener {
    /**
     * @priority HIGHEST
     */
    public function onEntityExplode(EntityExplodeEvent $event): void {
        foreach($event->getBlockList() as $block) {
            GameManager::addEntry(new BlockPlaceEntry($block));
        }
    }
}