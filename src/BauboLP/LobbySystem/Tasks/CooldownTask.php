<?php


namespace BauboLP\LobbySystem\Tasks;


use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\AnimationProvider;
use pocketmine\block\Block;
use pocketmine\entity\object\ItemEntity;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class CooldownTask extends Task
{

    private $i = 0;
    private $back = false;

    public function onRun(int $currentTick)
    {
        foreach (array_keys(AnimationProvider::$delayedTP) as $playerName) {
            if (($obj = LobbySystem::getPlayerCache($playerName)) != null) {
                if (time() > AnimationProvider::$delayedTP[$playerName]['time']) {
                    $obj->getPlayer()->teleport(AnimationProvider::$delayedTP[$playerName]['spawn']);
                    $obj->getPlayer()->playSound('firework.blast', 2);
                    $obj->getPlayer()->removeAllEffects();
                    unset(AnimationProvider::$delayedTP[$playerName]);
                }
            } else {
                unset(AnimationProvider::$delayedTP[$playerName]);
            }
        }

        if ($this->i > 6) $this->back = true;

        if ($this->back) {
            $this->i--;
            if ($this->i <= 0) {
                $this->back = false;
                $this->i++;
            }
        } else {
            $this->i++;
        }
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $player->setHealth($this->i);
            if(($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                if(!$obj->playingJumpAndRun()) {
                    if (Server::getInstance()->getDefaultLevel()->getBlock($player->getSide(0))->getId() == Block::SLIME) {
                        $level = $player->getLevel();
                        $x = $player->getX();
                        $z = $player->getZ();
                        $y = $player->getY();
                        $level->addParticle(new FlameParticle($player));
                        $level->addParticle(new LavaParticle($player));
                        $level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y, $z)));
                        $level->addParticle(new FlameParticle(new Vector3($x, $y, $z + 0.3)));
                        $level->addParticle(new FlameParticle(new Vector3($x + 0.5, $y, $z)));
                        $level->addParticle(new FlameParticle(new Vector3($x + 0.5, $y, $z + 0.2)));
                        $level->addParticle(new LavaParticle(new Vector3($x, $y + 0.5, $z)));
                        $level->addParticle(new LavaParticle(new Vector3($x + 0.2, $y + 0.2, $z)));
                        $level->addParticle(new LavaParticle(new Vector3($x, $y, $z + 0.3)));
                        $level->addParticle(new LavaParticle(new Vector3($x - 0.2, $y, $z)));
                        $level->addParticle(new LavaParticle(new Vector3($x, $y + 0.2, $z)));
                        $level->addParticle(new LavaParticle(new Vector3($x - 0.2, $y, $z + 0.3)));
                        $player->knockBack($player, 0, $player->getDirectionVector()->getX(), $player->getDirectionVector()->getZ(), 2.8);
                        $player->playSound('firework.launch', 2);
                    }
                }
            }
        }

        foreach (AnimationProvider::$itemsToKill as $id => $itemData) {
            if (time() > $itemData['time']) {
                $itemEntity = $itemData['entity'];
                if ($itemEntity instanceof ItemEntity) {
                    if (!$itemEntity->isClosed()) {
                        $itemEntity->close();
                        unset(AnimationProvider::$itemsToKill[$id]);
                    } else {
                        if (isset(AnimationProvider::$itemsToKill[$id]))
                            unset(AnimationProvider::$itemsToKill[$id]);
                    }
                }
            }
        }

        foreach (AnimationProvider::$blockReplace as $posData => $data) {
            if($data['time'] < time()) {
                $ex = explode(":", $posData);
                $pos = new Vector3((float)$ex[0], (float)$ex[1], (float)$ex[2]);

                if(isset($data['blockId']) && isset($data["blockMeta"]))
                Server::getInstance()->getDefaultLevel()->setBlock($pos, Block::get($data['blockId'], $data["blockMeta"]));

                unset(AnimationProvider::$blockReplace[$posData]);
            }
        }

        foreach (array_keys(AnimationProvider::$specialDelay) as $playerName) {
            if(time() > AnimationProvider::$specialDelay[$playerName]) {
                unset(AnimationProvider::$specialDelay[$playerName]);
            }
        }

        foreach (array_keys(AnimationProvider::$delay) as $playerName) {
            if(time() > AnimationProvider::$delay[$playerName]) {
                unset(AnimationProvider::$delay[$playerName]);
            }
        }
    }
}