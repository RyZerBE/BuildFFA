<?php


namespace BauboLP\BuildFFA\events;


use BauboLP\BuildFFA\provider\GameProvider;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\Player;

class InvTransactionListener implements Listener
{

    public function onInv(InventoryTransactionEvent $event)
    {
        $source = $event->getTransaction()->getSource();
        if($source instanceof Player) {
            if(($obj = GameProvider::getBuildFFAPlayer($source->getName())) != null) {
                if(!$obj->isSort()) {
                    $event->setCancelled();
                }
            }else {
                $event->setCancelled();
            }
        }
    }

    public function pickUp(InventoryPickupItemEvent $event)
    {
        $event->setCancelled();
    }
}