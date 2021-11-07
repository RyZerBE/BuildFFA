<?php


namespace BauboLP\BuildFFA\animation\type;


use BauboLP\BuildFFA\animation\Animation;
use BauboLP\BuildFFA\provider\GameProvider;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\Server;

class DestroyedBlockAnimation extends Animation
{
    /** @var Vector3 */
    private $vector3;

    public function __construct(Vector3 $vector3)
    {
        $this->vector3 = $vector3;
        parent::__construct();
    }

    public function tick()
    {
        if($this->getCurrentTick() >= 80) {

            $level = Server::getInstance()->getLevelByName(GameProvider::getMap());
            if (!Server::getInstance()->isLevelLoaded(GameProvider::getMap())) return;
            if($level->getBlock($this->vector3)->getId() != Block::AIR) return;

            $this->stop();
            $level = Server::getInstance()->getLevelByName(GameProvider::getMap());
            $level->setBlock($this->vector3, Block::get(Block::SANDSTONE));
            return;
        }
        parent::tick();
    }
}