<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;
use ryzerbe\buildffa\game\GameManager;
use ryzerbe\buildffa\player\BuildFFAPlayerManager;

class PlayerJoinListener implements Listener {
    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $event->setJoinMessage("");

        $player = $event->getPlayer();
        $bFFAPlayer = BuildFFAPlayerManager::addPlayer($player);

        $player->teleport(GameManager::getMap()?->getSpawnLocation(true) ?? Server::getInstance()->getDefaultLevel()->getSpawnLocation());
        $player->setImmobile();
        $bFFAPlayer->enterSafeZone();
    }
}