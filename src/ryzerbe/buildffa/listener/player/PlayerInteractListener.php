<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use ryzerbe\buildffa\player\BuildFFAPlayerManager;

class PlayerInteractListener implements Listener {
    /**
     * @priority HIGH
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void {
        if(BuildFFAPlayerManager::get($event->getPlayer())->isInSafeZone()) {
            $event->setCancelled();
        }
    }
}