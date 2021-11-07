<?php


namespace BauboLP\LobbySystem\Events;


use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\AnimationProvider;
use pocketmine\entity\Entity;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\math\Vector3;

class BlockPlaceBreakListener implements Listener
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

    public function onBlockPlace(BlockPlaceEvent $event)
    {
        $player = $event->getPlayer();
        if(($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
            if(!$obj->isBuildModeActivated()) {
                $event->setCancelled();
            }
        }else {
            $event->setCancelled();
        }
    }

    public function onBlocKBreak(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        if(($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
            if(!$obj->isBuildModeActivated()) {
                $event->setCancelled();
            }
        }else {
            $event->setCancelled();
        }
    }
}