<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\player;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use ryzerbe\buildffa\game\GameManager;
use ryzerbe\buildffa\player\BuildFFAPlayerManager;
use ryzerbe\core\player\PMMPPlayer;


class PlayerMoveListener implements Listener {
    public function onPlayerMove(PlayerMoveEvent $event): void {
    	/** @var PMMPPlayer $player */
        $player = $event->getPlayer();

        if($player->getY() <= 0) {
            $player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_VOID, 100));
            return;
        }

        if(GameManager::getMap()?->isInSpawnRadius($player)) {
            BuildFFAPlayerManager::get($player)?->enterSafeZone();
        } else {
            BuildFFAPlayerManager::get($player)?->leaveSafeZone();
        }
    }
}