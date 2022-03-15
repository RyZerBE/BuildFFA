<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;

class PlayerDropItemListener implements Listener {
    public function onItemDrop(PlayerDropItemEvent $event): void {
        $event->setCancelled();
    }
}