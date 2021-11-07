<?php


namespace BauboLP\BuildFFA\events;


use BauboLP\BuildFFA\provider\GameProvider;
use BauboLP\BuildFFA\provider\ItemProvider;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;

class InteractListener implements Listener
{

    public function interact(PlayerInteractEvent $event)
    {
        if($event->getBlock()->getId() === Block::BEACON || $event->getBlock()->getId() === Block::CHEST || $event->getBlock()->getId() === Block::ENDER_CHEST) {
            $event->setCancelled();
            return;
        }
        if(GameProvider::isVoting()) {
            if($event->getItem()->getId() == Item::MAP) { //Altay create a new map card...
                $event->setCancelled();
            }
            ItemProvider::execVoteItem($event->getPlayer());
        }else {
            ItemProvider::execGameItems($event->getPlayer());
        }
    }
}