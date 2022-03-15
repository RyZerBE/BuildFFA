<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\kit\item;

use pocketmine\item\Item;
use pocketmine\Player;
use ryzerbe\core\util\customitem\CustomItem;

class SpecialItem extends CustomItem {
    public function onUpdate(Player $player, Item $item, int $slot): void {}
}