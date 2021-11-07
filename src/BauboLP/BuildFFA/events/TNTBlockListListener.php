<?php


namespace BauboLP\BuildFFA\events;


use BauboLP\BuildFFA\provider\GameProvider;
use pocketmine\block\Block;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\Listener;

class TNTBlockListListener implements Listener
{

    public function tnt(EntityExplodeEvent $event)
    {
        $blockList = [];
        foreach ($event->getBlockList() as $block) {
            if($block->getId() === Block::SANDSTONE) {
                $blockList[] = $block;
                GameProvider::addBreakBlock($block->asVector3());
            }
        }

        $event->setBlockList($blockList);
    }
}