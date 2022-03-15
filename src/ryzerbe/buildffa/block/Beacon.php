<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\block;

use pocketmine\block\Beacon as PMBeacon;
use pocketmine\item\Item;
use pocketmine\Player;

class Beacon extends PMBeacon {
    public function onActivate(Item $item, Player $player = null): bool{
        return false;
    }
}