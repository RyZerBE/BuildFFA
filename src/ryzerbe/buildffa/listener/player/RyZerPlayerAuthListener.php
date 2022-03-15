<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\player;

use pocketmine\event\Listener;
use pocketmine\Server;
use ryzerbe\buildffa\game\GameManager;
use ryzerbe\core\event\player\RyZerPlayerAuthEvent;

class RyZerPlayerAuthListener implements Listener {
    public function onRyZerPlayerAuth(RyZerPlayerAuthEvent $event): void {
        $player = $event->getPlayer();
        $player->teleport(GameManager::getMap()?->getSpawnLocation(true) ?? Server::getInstance()->getDefaultLevel()->getSpawnLocation());
        $player->setImmobile(false);
    }
}