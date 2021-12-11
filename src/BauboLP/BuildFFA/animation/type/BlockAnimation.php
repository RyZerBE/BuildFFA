<?php


namespace BauboLP\BuildFFA\animation\type;


use BauboLP\BuildFFA\animation\Animation;
use BauboLP\BuildFFA\provider\GameProvider;
use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\item\Item;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class BlockAnimation extends Animation
{
    /** @var Vector3 */
    private Vector3 $blockPos;
    /** @var string */
    private string $playerName;

    public function __construct(Vector3 $blockPos, string $playerName)
    {
        $this->playerName = $playerName;
        $this->blockPos = $blockPos;
        parent::__construct();
    }

    public function tick(): void
    {
        $level = Server::getInstance()->getLevelByName(GameProvider::getMap());
        if (!Server::getInstance()->isLevelLoaded(GameProvider::getMap())) return;

        if ($this->getCurrentTick() > 100) {
            $this->stop();
            $block = $level->getBlock($this->blockPos);
            if ($block->getId() === BlockIds::SANDSTONE) return;

            $level->setBlock($this->blockPos, Block::get(BlockIds::AIR));
            $level->addParticle(new DestroyBlockParticle($this->blockPos, Block::get(BlockIds::REDSTONE_BLOCK)));

            if (!GameProvider::isVoting()) {
                if (($player = Server::getInstance()->getPlayerExact($this->playerName)) != null) {
                    if (($obj = GameProvider::getBuildFFAPlayer($player->getName())) != null) {
                        $sort = $obj->getInvSorts()[GameProvider::getKit()];
                        $item = $player->getInventory()->getItem($sort["blocks"]);
                        if ($item->getCount() < 64) {
                            $player->getInventory()->setItem($sort["blocks"], Item::get(BlockIds::RED_SANDSTONE, 0, $item->getCount() + 1)->setCustomName(TextFormat::GOLD . "Bausteine"));
                            $player->playSound('random.pop', 1, 1.0, [$player]);
                        }
                    }
                }
            }
            return;
        }

        if ($this->getCurrentTick() === 40) {
            $newBlock = Block::get(BlockIds::REDSTONE_BLOCK);
            $level->setBlock($this->blockPos, $newBlock);
            $pk = new LevelEventPacket();
            $pk->evid = LevelEventPacket::EVENT_BLOCK_START_BREAK;
            $pk->position = $this->blockPos;
            $pk->data = (int)round(65535 / 60);
            Server::getInstance()->broadcastPacket(Server::getInstance()->getOnlinePlayers(), $pk);
        }
        parent::tick();
    }
}