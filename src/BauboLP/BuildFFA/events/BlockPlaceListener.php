<?php


namespace BauboLP\BuildFFA\events;


use BauboLP\BuildFFA\animation\AnimationProvider;
use BauboLP\BuildFFA\animation\type\BlockAnimation;
use BauboLP\BuildFFA\animation\type\WebAnimation;
use BauboLP\BuildFFA\provider\GameProvider;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\Random;

class BlockPlaceListener implements Listener
{

    public function place(BlockPlaceEvent $event)
    {
        $block = $event->getBlock();
        if($event->getBlock()->distance(Server::getInstance()->getLevelByName(GameProvider::getMap())->getSafeSpawn()) <= 9) {
            $event->setCancelled();
            return;
        }
        if($block->getId() == Block::RED_SANDSTONE) {
            AnimationProvider::addActiveAnimation(new BlockAnimation($block->asVector3(), $event->getPlayer()->getName()));
        }else if($block->getId() == Block::WEB) {
            AnimationProvider::addActiveAnimation(new WebAnimation($block->asVector3(), $event->getPlayer()->getName()));
        }else if($block->getId() == Block::TNT) {
            $pos = $block->asVector3();
            $event->setCancelled();
            $mot = (new Random())->nextSignedFloat() * M_PI * 2;
            $nbt = Entity::createBaseNBT($pos->add(0.5, 0, 0.5), new Vector3(-sin($mot) * 0.02, 0.2, -cos($mot) * 0.02));
            $nbt->setShort("Fuse", 40);

            $tnt = Entity::createEntity(PrimedTNT::NETWORK_ID, Server::getInstance()->getLevelByName(GameProvider::getMap()), $nbt);
            $tnt->spawnToAll();
            $event->getItem()->pop();
        } else {
            $event->setCancelled();
        }
    }
}