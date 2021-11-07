<?php


namespace BauboLP\LobbySystem\Events;


use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\AnimationProvider;
use BlockHorizons\Fireworks\item\Fireworks;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class DropItemListener implements Listener
{

    public function onDrop(PlayerDropItemEvent $event)
    {
        $event->setCancelled();
        if($event->getItem()->getId() == Item::GOLD_NUGGET) {
            LobbySystem::createFirework($event->getPlayer()->asVector3(), Fireworks::TYPE_SMALL_SPHERE, Fireworks::COLOR_YELLOW);
            $event->getPlayer()->getInventory()->removeItem(Item::get(Item::GOLD_NUGGET, 0, 1));
            LobbySystem::getPlugin()->getScheduler()->scheduleDelayedTask(new class($event->getPlayer()->asVector3()) extends Task{
                /** @var \pocketmine\math\Vector3  */
                private $vec;

                public function __construct(Vector3 $vector3)
                {
                    $this->vec = $vector3;
                }

                /**
                 * @inheritDoc
                 */
                public function onRun(int $currentTick)
                {
                    $item = Item::get(Item::GOLD_NUGGET);

                    $posArray = [
                        $this->vec->add(0, 7, 0),
                        $this->vec->add(1, 7, 0),
                        $this->vec->add(0, 7, 1),
                        $this->vec->add(0, 7, 2),
                        $this->vec->add(2, 7, 0),
                        $this->vec->add(3, 7, 3),
                        $this->vec->add(4, 7, 0),
                        $this->vec->add(0, 7, 4),
                        $this->vec->add(5, 7, 0),
                        $this->vec->add(0, 7, 5),
                        $this->vec->add(1, 7, 5),
                        $this->vec->add(2, 7, 5),
                        $this->vec->add(3, 7, 2),
                        $this->vec->add(7, 7, 3),
                    ];

                    $level = Server::getInstance()->getDefaultLevel();
                    foreach ($posArray as $pos) {
                        $itemEntity = $level->dropItem($pos, $item);
                        AnimationProvider::$itemsToKill[$itemEntity->getId()] = ['entity' => $itemEntity, 'time' => time() + 60];
                    }

                    foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                        $player->playSound('random.explode', 5, 1.0, [$player]);
                    }
                }
            }, 40);
        }
    }
}