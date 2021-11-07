<?php


namespace BauboLP\LobbySystem\Events;


use BauboLP\Core\Provider\CoinProvider;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\Player;

class InventoryPickUpListener implements Listener
{

    public function onPickUp(InventoryPickupItemEvent $event)
    {
        $event->setCancelled();
        if ($event->getItem()->getItem()->getId() == Item::GOLD_NUGGET) {
            foreach ($event->getItem()->getViewers() as $player) {
                if ($player instanceof Player) {
                    if ($player->distance($event->getItem()->asVector3()) < 2) {
                        CoinProvider::addCoins($player->getName(), rand(5, 40));
                        $player->playSound('random.pop', 2, 1.0, [$player]);
                        break;
                    }
                }
            }
            $event->getItem()->close();
        }
    }
}