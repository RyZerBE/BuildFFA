<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use ryzerbe\buildffa\player\BuildFFAPlayerManager;

class PlayerQuitListener implements Listener {
    public function onPlayerQuit(PlayerQuitEvent $event): void {
        $event->setQuitMessage("");
        BuildFFAPlayerManager::removePlayer($event->getPlayer());
    }
}