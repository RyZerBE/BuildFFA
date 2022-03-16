<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\block;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\level\particle\SmokeParticle;
use ryzerbe\buildffa\game\entry\BlockBreakEntry;
use ryzerbe\buildffa\game\GameManager;
use ryzerbe\buildffa\game\kit\Kit;
use ryzerbe\core\util\ItemUtils;
use function intval;

class BlockPlaceListener implements Listener {
    public function onBlockPlace(BlockPlaceEvent $event): void {
        $player = $event->getPlayer();

        $map = GameManager::getMap();
        if($map === null || $map->isInSpawnRadius($player)) {
            $player->getLevel()->addParticle(new SmokeParticle($event->getBlock()->add(0.5, 0.5, 0.5)));
            $event->setCancelled();
            return;
        }
        $item = $event->getItem();
        $delay = 100;
        if(ItemUtils::hasItemTag($item, Kit::TAG_DESTROY_DELAY)) {
            $delay = intval(ItemUtils::getItemTag($item, Kit::TAG_DESTROY_DELAY));
        }
        if(ItemUtils::hasItemTag($item, Kit::TAG_INFINITE)) {
            $player->getInventory()->setItemInHand($item->setCount($item->getMaxStackSize()));
        }
        $block = $event->getBlock();
        GameManager::removeEntryById(Level::blockHash($block->getFloorX(), $block->getFloorY(), $block->getFloorZ()));
        GameManager::addEntry(new BlockBreakEntry($block, $delay));
    }
}