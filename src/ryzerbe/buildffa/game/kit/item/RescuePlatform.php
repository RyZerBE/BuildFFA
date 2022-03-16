<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\kit\item;

use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\Player;
use ryzerbe\buildffa\game\entry\BlockBreakEntry;
use ryzerbe\buildffa\game\GameManager;
use ryzerbe\core\player\PMMPPlayer;
use function mt_rand;

class RescuePlatform extends SpecialItem {
    public const LIFETIME = 150;

    public const COOLDOWN = 15;

    public function __construct(){
        parent::__construct(Item::get(ItemIds::BLAZE_ROD)->setCustomName("§r§6Rescue Platform"));
    }

    public function onInteract(PMMPPlayer $player, Item $item): void{
        if(!$item->equals($this->getItem(), true, false)) return;
        $newItem = Item::get(ItemIds::DYE, 8, self::COOLDOWN);
        $newItem->setNamedTag($item->getNamedTag());
        $newItem->setCustomName($item->getName());
        $player->getInventory()->setItemInHand($newItem);

        $player->teleport($player);

        $level = $player->getLevel();
        $center = $player->floor()->down(4);
        if($center->y > 0) {
            for($x = -1; $x <= 1; $x++) {
                for($z = -1; $z <= 1; $z++) {
                    $vector3 = $center->add($x, 0, $z);
                    if($level->getBlock($vector3)->getId() !== 0) {
                        continue;
                    }
                    $level->setBlock($vector3, Block::get(BlockIds::STAINED_GLASS, mt_rand(0, 15)));
                    GameManager::addEntry(new BlockBreakEntry($level->getBlock($vector3), self::LIFETIME));
                }
            }
        }
    }

    public function onUpdate(Player $player, Item $item, int $slot): void{
        if(--$item->count <= 0) {
            $player->getInventory()->setItem($slot, $this->getItem());
            return;
        }
        $player->getInventory()->setItem($slot, $item);
    }

    public function cancelInteract(): bool{
        return true;
    }
}