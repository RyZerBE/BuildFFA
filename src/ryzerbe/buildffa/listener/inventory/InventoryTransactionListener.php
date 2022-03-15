<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\listener\inventory;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Armor;
use ryzerbe\buildffa\player\BuildFFAPlayerManager;

class InventoryTransactionListener implements Listener {
    public function onInventoryTransaction(InventoryTransactionEvent $event): void {
        $player = $event->getTransaction()->getSource();
        $bFFAPlayer = BuildFFAPlayerManager::get($player);
        if($bFFAPlayer === null) {
            $event->setCancelled();
            return;
        }
        foreach($event->getTransaction()->getActions() as $action) {
            if(!$action instanceof SlotChangeAction) continue;
            $sourceItem = $action->getSourceItem();
            $targetItem = $action->getTargetItem();

            if(
                $sourceItem instanceof Armor ||
                $targetItem instanceof Armor ||
                !$bFFAPlayer->sortsInventory()
            ) {
                $event->setCancelled();
                return;
            }
        }
    }
}