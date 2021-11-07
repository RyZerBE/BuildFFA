<?php


namespace BauboLP\BuildFFA\events;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;

class DropItemListener implements Listener
{

    public function dropItem(PlayerDropItemEvent $event)
    {
        $event->setCancelled();
    }
}