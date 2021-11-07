<?php


namespace BauboLP\LobbySystem\Tasks;


use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\AnimationProvider;
use pocketmine\block\Block;
use pocketmine\block\Button;
use pocketmine\block\Fence;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\Trapdoor;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\tile\FlowerPot;

class WalkingBlockTask extends Task
{

    public function onRun(int $currentTick)
    {
        if(AnimationProvider::$addonBlocker == true) return;

        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if(($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
              if(!$obj->playingJumpAndRun() && $obj->isAddonsActivated()) {
                  if($obj->getWalkingBlock() != null && $obj->getWalkingBlock() != "") {
                      $wb = $obj->getWalkingBlock();
                      if($wb == "Glasses") {
                          $this->drawWalkingBlocks($player, [Block::get(Block::STAINED_GLASS, mt_rand(1, 14)), Block::get(Block::STAINED_GLASS, mt_rand(1, 14)), Block::get(Block::STAINED_GLASS, mt_rand(1, 14)), Block::get(Block::STAINED_GLASS, mt_rand(1, 14)), Block::get(Block::STAINED_GLASS, mt_rand(1, 14)), Block::get(Block::STAINED_GLASS, mt_rand(1, 14)), Block::get(Block::STAINED_GLASS, mt_rand(1, 14))]);
                      }else if($wb == "Rich Rich") {
                          $this->drawWalkingBlocks($player, [Block::get(Block::GOLD_BLOCK), Block::get(Block::EMERALD_BLOCK), Block::get(Block::DIAMOND_BLOCK), Block::get(Block::IRON_BLOCK)]);
                      }else if($wb == "Ice Cold") {
                          $this->drawWalkingBlocks($player, [Block::get(Block::FROSTED_ICE), Block::get(Block::SNOW_BLOCK), Block::get(Block::ICE)]);
                      }else if($wb == "Farmer") {
                          $this->drawWalkingBlocks($player, [Block::get(Block::HAY_BLOCK), Block::get(Block::MELON_BLOCK), Block::get(Block::PUMPKIN), Block::get(Block::GRASS)]);
                      }else if($wb == "Concrete") {
                          $this->drawWalkingBlocks($player, [Block::get(Block::CONCRETE, mt_rand(1, 14)), Block::get(Block::CONCRETE, mt_rand(1, 14)), Block::get(Block::CONCRETE, mt_rand(1, 14)), Block::get(Block::CONCRETE, mt_rand(1, 14)), Block::get(Block::CONCRETE, mt_rand(1, 14)), Block::get(Block::CONCRETE, mt_rand(1, 14))]);
                      }else if($wb == "BedWars") {
                          $this->drawWalkingBlocks($player, [Block::get(Block::SANDSTONE), Block::get(Block::RED_SANDSTONE), Block::get(Block::END_STONE)]);
                      }
                  }
              }
            }
        }
    }

    /**
     * @param \pocketmine\Player $player
     * @param Block[] $blockList
     */
    private function drawWalkingBlocks(Player $player, array $blockList)
    {
        for($x = -1; $x <= 1; $x++){
            for($z = -1; $z <= 1; $z++){
                $vec = $player->add($x, -0.8, $z)->floor();
                $block = $player->getLevel()->getBlockAt($vec->x, $vec->y, $vec->z);
                if($block->getId() === Block::AIR || isset(AnimationProvider::$blockReplace["{$block->x}:{$block->y}:{$block->z}"]) || $block->getId() === Block::CARPET || $block->getId() === Block::SNOW_LAYER || $block instanceof FlowerPot || $block instanceof Trapdoor || $block->getId() === Block::WALL_SIGN
                    || $block->getId() === Block::SIGN_POST || $block->getId() === Block::TALLGRASS ||
                    $block->getId() === Block::DOUBLE_PLANT || $block->getId() === Block::WHEAT_BLOCK || $block instanceof Stair ||
                    $block instanceof Slab || $block instanceof Button || $block instanceof Fence || $block->getId() === Block::SLIME
                    || $block->getId() === Block::PORTAL || $block->getId() === Block::ITEM_FRAME_BLOCK || $block->getId() === Block::BED_BLOCK || $block->getId() === Block::SEA_LANTERN)
                    continue;

                $player->getLevel()->setBlock($block->asVector3(), $blockList[array_rand($blockList)], false, false);
                AnimationProvider::$blockReplace["{$block->x}:{$block->y}:{$block->z}"] = ['time' => time() + 0.3, 'blockId' => $block->getId(), 'blockMeta' => $block->getDamage()];
            }
        }
    }
}