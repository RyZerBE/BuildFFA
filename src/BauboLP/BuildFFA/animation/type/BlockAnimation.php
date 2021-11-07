<?php


namespace BauboLP\BuildFFA\animation\type;


use BauboLP\BuildFFA\animation\Animation;
use BauboLP\BuildFFA\provider\GameProvider;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class BlockAnimation extends Animation
{
    /** @var Block */
    private $block;
    /** @var string */
    private $playerName;

    public function __construct(Block $block, string $playerName)
    {
        $this->playerName = $playerName;
        $this->block = $block;
        parent::__construct();
    }

    public function tick(): void
    {
        $level = Server::getInstance()->getLevelByName(GameProvider::getMap());
        if (!Server::getInstance()->isLevelLoaded(GameProvider::getMap())) return;

        if ($this->getCurrentTick() > 100) {
            $this->stop();
            $block = $level->getBlock($this->block->asVector3());
            if ($block->getId() === Block::SANDSTONE) return;

            $level->setBlock($this->block->asVector3(), Block::get(Block::AIR));
            $level->addParticle(new DestroyBlockParticle($this->block->asVector3(), Block::get(Block::REDSTONE_BLOCK)));


            if (!GameProvider::isVoting()) {
                if (($player = Server::getInstance()->getPlayerExact($this->playerName)) != null) {
                    if (($obj = GameProvider::getBuildFFAPlayer($player->getName())) != null) {
                        $sort = $obj->getInvSorts()[GameProvider::getKit()];
                        $item = $player->getInventory()->getItem($sort["blocks"]);
                        if ($item->getCount() < 64) {
                            $player->getInventory()->setItem($sort["blocks"], Item::get(Item::RED_SANDSTONE, 0, $item->getCount() + 1)->setCustomName(TextFormat::GOLD . "Bausteine"));
                            $player->playSound('random.pop', 1, 1.0, [$player]);
                        }
                    }
                }
            }
            return;
        }

        if ($this->getCurrentTick() === 40) {
            $newBlock = Block::get(Block::REDSTONE_BLOCK);
            $level->setBlock($this->block->asVector3(), $newBlock);
            $pk = new LevelEventPacket();
            $pk->evid = LevelEventPacket::EVENT_BLOCK_START_BREAK;
            $pk->position = $this->block->asVector3();
            $pk->data = (int)round(65535 / 60);
            Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
        }
        parent::tick();
    }
}