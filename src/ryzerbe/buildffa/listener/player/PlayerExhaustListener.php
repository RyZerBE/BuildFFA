<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;

class PlayerExhaustListener implements Listener {
    public function onPlayerExhaust(PlayerExhaustEvent $event): void {
        $event->getPlayer()->setFood($event->getPlayer()->getMaxFood());
    }
}