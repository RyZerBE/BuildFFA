<?php


namespace BauboLP\BuildFFA\tasks;


use BauboLP\BuildFFA\BuildFFA;
use BauboLP\BuildFFA\provider\GameProvider;
use ryzerbe\core\language\LanguageProvider;
use pocketmine\block\Block;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class DelayTask extends Task
{

    public function onRun(int $currentTick)
    {
        $server = Server::getInstance();
        $level = $server->getLevelByName(GameProvider::getMap());

        foreach ($server->getOnlinePlayers() as $player) {
            if (($obj = GameProvider::getBuildFFAPlayer($player->getName())) != null) {
                if ($obj->getLastHit() < time() && $obj->getCombo() != 0)
                    $obj->resetCombo();
            }
        }

        foreach ($level->getEntities() as $entity) {
            if($entity instanceof ItemEntity) {
                if(array_key_exists($entity->getId(), GameProvider::$removeItems)) {
                    if(GameProvider::$removeItems[$entity->getId()] < time()) {
                        $entity->kill();
                    }
                }
            }
        }
    }
}