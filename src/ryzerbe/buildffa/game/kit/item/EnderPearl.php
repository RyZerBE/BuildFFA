<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\kit\item;

use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\Player;
use ryzerbe\buildffa\player\BuildFFAPlayerManager;
use ryzerbe\core\player\PMMPPlayer;

class EnderPearl extends SpecialItem {
    public const COOLDOWN = 15;

    public function __construct(){
        parent::__construct(Item::get(ItemIds::ENDER_PEARL)->setCustomName("§r§6Ender Pearl"));
    }

    public function onInteract(PMMPPlayer $player, Item $item): void{
        if(
            !$item->equals($this->getItem(), true, false) ||
            BuildFFAPlayerManager::get($player)?->isInSafeZone()
        ) return;
        $newItem = Item::get(ItemIds::DYE, 8, self::COOLDOWN);
        $newItem->setNamedTag($item->getNamedTag());
        $newItem->setCustomName($item->getName());
        $player->getInventory()->setItemInHand($newItem);
    }

    public function onUpdate(Player $player, Item $item, int $slot): void{
        if(--$item->count <= 0) {
            $player->getInventory()->setItem($slot, $this->getItem());
            return;
        }
        $player->getInventory()->setItem($slot, $item);
    }

    public function cancelInteract(): bool{
        return false;
    }
}