<?php


namespace BauboLP\BuildFFA\animation\type;


use BauboLP\BuildFFA\animation\Animation;
use BauboLP\BuildFFA\provider\GameProvider;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class WebAnimation extends Animation
{
    /** @var string */
    private string $playerName;
    /** @var Vector3 */
    private Vector3 $blockPos;

    public function __construct(Vector3 $blockPos, string $playerName)
    {
        $this->playerName = $playerName;
        $this->blockPos = $blockPos;
        parent::__construct();
    }

    public function tick()
    {
        if ($this->getCurrentTick() === 100) {
            $level = Server::getInstance()->getLevelByName(GameProvider::getMap());
            if (!Server::getInstance()->isLevelLoaded(GameProvider::getMap())) return;

            $block = $level->getBlock($this->blockPos);
            if ($block->getId() === Block::SANDSTONE) return;
                $level->setBlock($this->blockPos, Block::get(Block::AIR));
                $level->addParticle(new DestroyBlockParticle($this->blockPos, Block::get(Block::WEB)));


            if (!GameProvider::isVoting()) {
                if (($player = Server::getInstance()->getPlayerExact($this->playerName)) != null) {
                    if (($obj = GameProvider::getBuildFFAPlayer($player->getName())) != null) {
                        $sort = $obj->getInvSorts()[GameProvider::getKit()];
                        $item = $player->getInventory()->getItem($sort["webs"]);
                        if ($item->getCount() < 3) {
                            $player->getInventory()->setItem($sort["webs"], Item::get(Item::WEB, 0, $item->getCount() + 1)->setCustomName(TextFormat::GOLD . "Web"));
                            $player->playSound('random.pop', 1, 1.0, [$player]);
                        }
                    }
                }
            }
            $this->stop();
            return;
        }

        if($this->getCurrentTick() === 0) {
            $pk = new LevelEventPacket();
            $pk->evid = LevelEventPacket::EVENT_BLOCK_START_BREAK;
            $pk->position = $this->blockPos;
            $pk->data = (int)round(65535 / 100);
            Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
        }
        parent::tick();
    }
}