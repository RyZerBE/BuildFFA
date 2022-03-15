<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerToggleSneakEvent;
use ryzerbe\buildffa\player\BuildFFAPlayerManager;

class PlayerToggleSneakListener implements Listener {
    public function onPlayerToggleSneak(PlayerToggleSneakEvent $event): void {
        BuildFFAPlayerManager::get($event->getPlayer())?->finishInventorySort();
    }
}