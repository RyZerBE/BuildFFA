<?php


namespace BauboLP\BuildFFA\events;


use BauboLP\BuildFFA\animation\AnimationProvider;
use BauboLP\BuildFFA\animation\type\DestroyedBlockAnimation;
use BauboLP\BuildFFA\provider\GameProvider;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\Server;

class BlockBreakListener implements Listener
{

    /**
     * @param InventoryTransactionEvent $event
     * @priority MONITOR
     */
    public function ChestInvFix(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $player = $transaction->getSource();
        foreach ($transaction->getActions() as $action) {
            if ($action instanceof SlotChangeAction && $event->isCancelled()) $action->getInventory()->sendSlot($action->getSlot(), $player);
        }
    }

    public function break(BlockBreakEvent $event)
    {
        $block = $event->getBlock();
        if ($block->distance(Server::getInstance()->getLevelByName(GameProvider::getMap())->getSafeSpawn()) <= 8) {
            $event->setCancelled();
            return;
        }
        if ($block->getId() === Block::SANDSTONE) {
            AnimationProvider::addActiveAnimation(new DestroyedBlockAnimation($block->asVector3()));
            $event->setDrops([]);
        }else if($block->getId() === Block::WEB) {
            $event->setDrops([]);
        }else {
            $event->setCancelled();
        }
    }

    public function craft(CraftItemEvent $event)
    {
        $event->setCancelled();
    }
}