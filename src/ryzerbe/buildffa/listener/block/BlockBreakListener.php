<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\block;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\level\particle\SmokeParticle;
use ryzerbe\buildffa\game\entry\BlockPlaceEntry;
use ryzerbe\buildffa\game\GameManager;

class BlockBreakListener implements Listener {
    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();

        $map = GameManager::getMap();
        if($map === null || $map->isInSpawnRadius($player)) {
            $player->getLevel()->addParticle(new SmokeParticle($event->getBlock()->add(0.5, 0.5, 0.5)));
            $player->playSound("note.bass", 5.0, 1.0, [$player]);
            $event->setCancelled();
            return;
        }
        $event->setDrops([]);
        $event->setXpDropAmount(0);

        $block = $event->getBlock();
        GameManager::removeEntryById(Level::blockHash($block->getFloorX(), $block->getFloorY(), $block->getFloorZ()));
        GameManager::addEntry(new BlockPlaceEntry($block));
    }
}