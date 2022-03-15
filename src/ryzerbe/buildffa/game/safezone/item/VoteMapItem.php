<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\safezone\item;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use ryzerbe\buildffa\game\safezone\form\VoteMapForm;
use ryzerbe\core\player\PMMPPlayer;
use ryzerbe\core\util\customitem\CustomItem;

class VoteMapItem extends CustomItem {
    public function __construct(){
        parent::__construct(ItemFactory::get(ItemIds::EMPTY_MAP)->setCustomName("§r§aVote Map"), 3);
    }

    public function onInteract(PMMPPlayer $player, Item $item): void{
        if($player->hasItemCooldown($item)) return;
        $player->resetItemCooldown($item, 10);
        VoteMapForm::open($player);
    }
}