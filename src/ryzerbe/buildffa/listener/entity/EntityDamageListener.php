<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\entity;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use ryzerbe\buildffa\game\GameManager;
use ryzerbe\buildffa\player\BuildFFAPlayerManager;

class EntityDamageListener implements Listener {
    public function onEntityDamage(EntityDamageEvent $event): void {
        $player = $event->getEntity();
        if(!$player instanceof Player) return;

        $map = GameManager::getMap();
        if($map === null || $map->isInSpawnRadius($player) || $event->getCause() === EntityDamageEvent::CAUSE_FALL) {
            $event->setCancelled();
            return;
        }

        $bFFAPlayer = BuildFFAPlayerManager::get($player);
        if($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            if(!$damager instanceof Player) {
                $damager = $damager->getOwningEntity();
            }

            if($damager instanceof Player) {
                $bFFAPlayer->setLastDamager($damager);
            }
        }

        if($event->getFinalDamage() >= $player->getHealth()) {
            $bFFAPlayer->onDeath();
            $event->setCancelled();
        }
    }
}