<?php


namespace BauboLP\BuildFFA\provider;


use BauboLP\BuildFFA\animation\AnimationProvider;
use BauboLP\BuildFFA\animation\type\BlockAnimation;
use BauboLP\BuildFFA\BuildFFA;
use BauboLP\BuildFFA\forms\voting\KitVoteForm;
use BauboLP\BuildFFA\forms\voting\MapFormVote;
use BauboLP\BuildFFA\forms\voting\SkipConfirmForm;
use BauboLP\BuildFFA\tasks\AnimationTask;
use BauboLP\BuildFFA\utils\Kits;
use BauboLP\BW\API\GameAPI;
use baubolp\core\provider\LanguageProvider;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class ItemProvider extends Kits
{
    /** @var array  */
    public static $delay = [];

    /**
     * @param \pocketmine\Player $player
     */
    public static function clearAllInvs(Player $player)
    {
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCraftingGrid()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getUIInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();
        $player->removeAllEffects();
    }

    /**
     * @param \pocketmine\Player $player
     */
    public static function giveVoteItems(Player $player)
    {
        self::clearAllInvs($player);
        $mapItem = Item::get(Item::MAP, 0, 1)->setCustomName(TextFormat::GOLD."Map-Voting");
        $kitItem = Item::get(Item::NETHER_STAR, 0, 1)->setCustomName(TextFormat::GOLD."Kit-Voting");
        $skip = Item::get(Item::DYE, 14, 1)->setCustomName(TextFormat::GOLD."Skip");

        $inv = $player->getInventory();
        $inv->setItem(0, $mapItem);
        $inv->setItem(4, $kitItem);
        $inv->setItem(8, $skip);
    }

    /**
     * @param \pocketmine\Player $player
     */
    public static function execVoteItem(Player $player)
    {
        $item = $player->getInventory()->getItemInHand()->getId();
        if(empty(self::$delay[$player->getName()]))
            self::$delay[$player->getName()] = microtime(true);

        if(self::$delay[$player->getName()] > microtime(true)) return;

        switch ($item) {
            case Item::DYE:
                self::$delay[$player->getName()] = microtime(true) + 0.5;
                $player->sendForm(new SkipConfirmForm($player->getName()));
               break;
            case Item::MAP:
                self::$delay[$player->getName()] = microtime(true) + 0.5;
                $player->sendForm(new MapFormVote());
                break;
            case Item::NETHER_STAR:
                self::$delay[$player->getName()] = microtime(true) + 0.5;
                $player->sendForm(new KitVoteForm());
                break;
        }
    }

    public static function execGameItems(Player $player)
    {
        $item = $player->getInventory()->getItemInHand()->getId();
        switch ($item) {
            case Item::BLAZE_ROD:
                $level = Server::getInstance()->getLevelByName(GameProvider::getMap());
                if($level->getBlock($player->getSide(0))->getId() != Block::AIR || $level->getBlock(new Vector3($player->x, $player->y-2, $player->z))->getId() != Block::AIR) {
                    #$player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('rettungsplattform-cant-place-block', $player->getName()));
                    $player->resetItemCooldown($player->getInventory()->getItemInHand(), 10);
                    return;
                }
                $block = Block::get(Block::GLASS, 0);
                $blocks = [];
                $x = $player->getPlayer()->getX();
                $y = $player->getPlayer()->getY();
                $z = $player->getPlayer()->getZ();
                $y = $y - 6;
                $tppos = new Vector3($x, $y, $z);
                if($player->getPlayer()->getLevel()->getBlock($tppos)->getId() == Block::AIR) {
                    $level->setBlock($tppos, $block);
                    $blocks[] = $level->getBlock($tppos);
                }
                $x = $player->getPlayer()->getX() + 1;
                $y = $player->getPlayer()->getY();
                $z = $player->getPlayer()->getZ();
                $y = $y - 6;
                $pos = new Vector3($x, $y, $z);
                if($player->getPlayer()->getLevel()->getBlock($pos)->getId() == Block::AIR) {
                    $level->setBlock($pos, $block);
                    $blocks[] = $level->getBlock($pos);
                }

                $x = $player->getPlayer()->getX() - 1;
                $y = $player->getPlayer()->getY();
                $z = $player->getPlayer()->getZ();
                $y = $y - 6;
                $pos = new Vector3($x, $y, $z);
                if($player->getPlayer()->getLevel()->getBlock($pos)->getId() == Block::AIR) {
                    $level->setBlock($pos, $block);
                    $blocks[] = $level->getBlock($pos);
                }
                $x = $player->getPlayer()->getX();
                $y = $player->getPlayer()->getY();
                $z = $player->getPlayer()->getZ() - 1;
                $y = $y - 6;
                $pos = new Vector3($x, $y, $z);
                if($player->getPlayer()->getLevel()->getBlock($pos)->getId() == Block::AIR) {
                    $level->setBlock($pos, $block);
                    $blocks[] = $level->getBlock($pos);
                }

                $x = $player->getPlayer()->getX();
                $y = $player->getPlayer()->getY();
                $z = $player->getPlayer()->getZ() + 1;
                $y = $y - 6;
                $pos = new Vector3($x, $y, $z);
                if($player->getPlayer()->getLevel()->getBlock($pos)->getId() == Block::AIR) {
                    $level->setBlock($pos, $block);
                    $blocks[] = $level->getBlock($pos);
                }
                $x = $player->getPlayer()->getX() + 1;
                $y = $player->getPlayer()->getY();
                $z = $player->getPlayer()->getZ() + 1;
                $y = $y - 6;
                $pos = new Vector3($x, $y, $z);
                if($player->getPlayer()->getLevel()->getBlock($pos)->getId() == Block::AIR) {
                    $level->setBlock($pos, $block);
                    $blocks[] = $level->getBlock($pos);
                }
                $x = $player->getPlayer()->getX() - 1;
                $y = $player->getPlayer()->getY();
                $z = $player->getPlayer()->getZ() - 1;
                $y = $y - 6;
                $pos = new Vector3($x, $y, $z);
                if($player->getPlayer()->getLevel()->getBlock($pos)->getId() == Block::AIR) {
                    $level->setBlock($pos, $block);
                    $blocks[] = $level->getBlock($pos);
                }
                $x = $player->getPlayer()->getX() + 1;
                $y = $player->getPlayer()->getY();
                $z = $player->getPlayer()->getZ() - 1;
                $y = $y - 6;
                $pos = new Vector3($x, $y, $z);
                if($player->getPlayer()->getLevel()->getBlock($pos)->getId() == Block::AIR) {
                    $level->setBlock($pos, $block);
                    $blocks[] = $level->getBlock($pos);
                }
                $x = $player->getPlayer()->getX() - 1;
                $y = $player->getPlayer()->getY();
                $z = $player->getPlayer()->getZ() + 1;
                $y = $y - 6;
                $pos = new Vector3($x, $y, $z);
                if($player->getPlayer()->getLevel()->getBlock($pos)->getId() == Block::AIR) {
                    $level->setBlock($pos, $block);
                    $blocks[] = $level->getBlock($pos);
                }
                $x = $player->getPlayer()->getX() + 2;
                $y = $player->getPlayer()->getY();
                $z = $player->getPlayer()->getZ();
                $y = $y - 6;
                $pos = new Vector3($x, $y, $z);
                if($player->getPlayer()->getLevel()->getBlock($pos)->getId() == Block::AIR) {
                    $level->setBlock($pos, $block);
                    $blocks[] = $level->getBlock($pos);
                }
                $x = $player->getPlayer()->getX() - 2;
                $y = $player->getPlayer()->getY();
                $z = $player->getPlayer()->getZ();
                $y = $y - 6;
                $pos = new Vector3($x, $y, $z);
                if($player->getPlayer()->getLevel()->getBlock($pos)->getId() == Block::AIR) {
                    $level->setBlock($pos, $block);
                    $blocks[] = $level->getBlock($pos);
                }
                $x = $player->getPlayer()->getX();
                $y = $player->getPlayer()->getY();
                $z = $player->getPlayer()->getZ() + 2;
                $y = $y - 6;
                $pos = new Vector3($x, $y, $z);
                if($player->getPlayer()->getLevel()->getBlock($pos)->getId() == Block::AIR) {
                    $level->setBlock($pos, $block);
                    $blocks[] = $level->getBlock($pos);
                }
                $x = $player->getPlayer()->getX();
                $y = $player->getPlayer()->getY();
                $z = $player->getPlayer()->getZ() - 2;
                $y = $y - 6;
                $pos = new Vector3($x, $y, $z);
                if($player->getPlayer()->getLevel()->getBlock($pos)->getId() == Block::AIR) {
                    $level->setBlock($pos, $block);
                    $blocks[] = $level->getBlock($pos);;
                }
                foreach ($blocks as $block)
                    AnimationProvider::addActiveAnimation(new BlockAnimation($block, "PimmelBerger"));


                if(($obj = GameProvider::getBuildFFAPlayer($player->getName())) != null) {
                    $obj->addRPCooldown();
                }

                $sort = $obj->getInvSorts()[GameProvider::getKit()];
                $player->getInventory()->setItem($sort["rp"], Item::get(Item::STICK, 0, 1)->setCustomName(TextFormat::RED."Rettungsplattform"));

                $player->getPlayer()->teleport(new Vector3($tppos->x, $pos->y + 2, $tppos->z));
                break;
        }
    }
}