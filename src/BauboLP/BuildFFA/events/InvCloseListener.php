<?php


namespace BauboLP\BuildFFA\events;


use BauboLP\BuildFFA\BuildFFA;
use BauboLP\BuildFFA\provider\GameProvider;
use BauboLP\BuildFFA\utils\Kits;
use ryzerbe\core\language\LanguageProvider;
use muqsit\invmenu\inventories\BaseFakeInventory;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

class InvCloseListener implements Listener
{

    public function closeInv(InventoryCloseEvent $event)
    {
        $inv = $event->getInventory();
        $player = $event->getPlayer();
        if($inv instanceof BaseFakeInventory or $inv instanceof \muqsit\invmenu\inventories\ChestInventory) {
           if(($obj = GameProvider::getBuildFFAPlayer($player->getName())) != null) {
               if($obj->isSort()) {
                   $save = true;
                   foreach ($player->getInventory()->getContents(true) as $slot => $item) {
                       if($item->getId() != Item::AIR) {
                           $save = false;
                       }
                   }

                   $translateToInvSlot = [
                        9 => 0,
                        10 => 1,
                        11 => 2,
                        12 => 3,
                        13 => 4,
                        14 => 5,
                        15 => 6,
                        16 => 7,
                        17 => 8
                   ];

                   if($save) {
                       $canBeSave = [9, 10, 11, 12, 13, 14, 15, 16, 17];

                       $sword = null;
                       $stick = null;
                       $blocks = null;
                       $webs = null;
                       $kitItem = null;
                       $rp = null;
                       $ep = null;
                       foreach ($inv->getContents(true) as $slot => $item) {
                           if($item->getId() != Item::AIR && $item->getId() != Item::GLASS_PANE) {
                               if($item->getId() == Item::GOLD_SWORD) {
                                   if(in_array($slot, $canBeSave)) {
                                      $sword = $translateToInvSlot[$slot];
                                   }
                               }else if($item->getId() == Item::STICK) {
                                   if(in_array($slot, $canBeSave)) {
                                       $stick = $translateToInvSlot[$slot];
                                   }
                               }else if($item->getId() == Item::RED_SANDSTONE) {
                                   if(in_array($slot, $canBeSave)) {
                                       $blocks = $translateToInvSlot[$slot];
                                   }
                               }else if($item->getId() == Item::WEB) {
                                   if(in_array($slot, $canBeSave)) {
                                       $webs = $translateToInvSlot[$slot];
                                   }
                               }else if($item->getId() == Item::TNT || $item->getId() == Item::FISHING_ROD || $item->getId() == Item::SNOWBALL || $item->getId() == Item::IRON_PICKAXE || $item->getId() == Item::BOW) {
                                   if(in_array($slot, $canBeSave)) {
                                       $kitItem = $translateToInvSlot[$slot];
                                   }
                               }else if($item->getId() == Item::BLAZE_ROD) {
                                   if(in_array($slot, $canBeSave)) {
                                       $rp = $translateToInvSlot[$slot];
                                   }
                               }else if($item->getId() == Item::ENDER_PEARL) {
                                   if(in_array($slot, $canBeSave)) {
                                       $ep = $translateToInvSlot[$slot];
                                   }
                               }
                           }
                       }

                       if($sword == null || $kitItem == null || $webs == null || $stick == null || $ep == null || $rp == null || $blocks == null) {
                           $sort = explode(":", "$sword:$stick:$blocks:$webs:$kitItem:$rp:$ep");
                           $obj->setSort(false);
                           #$obj->setInvSort($sort);
                           $newInvSort = $obj->getInvSorts();
                           $newInvSort[GameProvider::getKit()] = ["blocks" => $sort[2], "stick" => $sort[1], "sword" => $sort[0], "webs" => $sort[3], "kit-item" => $sort[4], "rp" => $sort[5], "ep" => $sort[6]];
                           $obj->setInvSorts($newInvSort);
                           $obj->giveItems();
                           $obj->getPlayer()->playSound('random.levelup', 5, 1.0, [$player]);
                       }else {
                           $obj->giveItems();
                           $obj->getPlayer()->playSound('random.bass', 1, 1.0, [$obj->getPlayer()]);
                           $player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('something-went-wrong', $player->getName()));
                       }
                   }else {
                       $obj->giveItems();
                       $obj->getPlayer()->playSound('random.bass', 1, 1.0, [$obj->getPlayer()]);
                       $player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('something-went-wrong', $player->getName()));
                   }
               }
           }
        }
    }
}