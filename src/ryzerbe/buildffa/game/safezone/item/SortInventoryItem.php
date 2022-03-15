<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\safezone\item;

use pocketmine\block\BlockIds;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use ryzerbe\buildffa\game\safezone\form\SortInventoryForm;
use ryzerbe\core\player\PMMPPlayer;
use ryzerbe\core\util\customitem\CustomItem;

class SortInventoryItem extends CustomItem {
    public function __construct(){
        parent::__construct(ItemFactory::get(BlockIds::CHEST)->setCustomName("§r§aSort Inventory"), 0);
    }

    public function onInteract(PMMPPlayer $player, Item $item): void{
        if($player->hasItemCooldown($item)) return;
        $player->resetItemCooldown($item, 10);

        SortInventoryForm::open($player);
    }
}