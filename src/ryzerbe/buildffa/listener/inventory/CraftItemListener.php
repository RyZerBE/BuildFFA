<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\inventory;

use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\Listener;

class CraftItemListener implements Listener {
    public function onCraftItem(CraftItemEvent $event): void {
        $event->setCancelled();
    }
}