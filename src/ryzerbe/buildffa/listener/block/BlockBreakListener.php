<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\block;

use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\level\particle\SmokeParticle;
use ryzerbe\buildffa\game\entry\BlockPlaceEntry;
use ryzerbe\buildffa\game\GameManager;

class BlockBreakListener implements Listener {

    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
		$block = $event->getBlock();

        $map = GameManager::getMap();
        if($map === null || $map->isInSpawnRadius($player) || $block->getId() === BlockIds::SEA_LANTERN) {
            $player->getLevel()->addParticle(new SmokeParticle($event->getBlock()->add(0.5, 0.5, 0.5)));
            $player->playSound("note.bass", 5.0, 1.0, [$player]);
            $event->setCancelled();
            return;
        }
        $event->setDrops([]);
        $event->setXpDropAmount(0);
		if(GameManager::entryExists(Level::blockHash($block->getFloorX(), $block->getFloorY(), $block->getFloorZ()))) {
			GameManager::removeEntryById(Level::blockHash($block->getFloorX(), $block->getFloorY(), $block->getFloorZ()));
			return;
		}
		GameManager::addEntry(new BlockPlaceEntry($block));
		GameManager::removeEntryById(Level::blockHash($block->getFloorX(), $block->getFloorY(), $block->getFloorZ()));
    }
}