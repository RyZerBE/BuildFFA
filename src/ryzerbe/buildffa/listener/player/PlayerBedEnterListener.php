<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBedEnterEvent;

class PlayerBedEnterListener implements Listener {
    public function onPlayerBedEnter(PlayerBedEnterEvent $event): void {
        $event->setCancelled();
    }
}