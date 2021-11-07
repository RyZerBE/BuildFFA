<?php


namespace BauboLP\LobbySystem\Events;


use BauboLP\Cloud\Bungee\BungeeAPI;
use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\Core\Utils\Emotes;
use BauboLP\LobbySystem\Forms\BuyLottoTicketForm;
use BauboLP\LobbySystem\Forms\ReplayForm;
use BauboLP\LobbySystem\Forms\RunningClanWarForm;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\AnimationProvider;
use BauboLP\LobbySystem\Provider\ItemProvider;
use BauboLP\LobbySystem\Provider\NPCProvider;
use BauboLP\LobbySystem\Utils\Math;
use BauboLP\NPCSystem\entity\Geometry;
use BauboLP\NPCSystem\entity\NPC;
use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\entity\projectile\Projectile;
use pocketmine\entity\projectile\Snowball;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEntityEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class InteractListener implements Listener
{

    private $notInteractAbleBlocks = [Block::FURNACE, Block::CRAFTING_TABLE, Block::DROPPER, Block::SHULKER_BOX, Block::CHEST, Block::BEACON, Block::ANVIL];

    public function onInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        ItemProvider::execItem($event->getPlayer());
        if (in_array($event->getBlock()->getId(), $this->notInteractAbleBlocks)) {
            $event->setCancelled(); //players put their items into the tile
            return;
        }
        if ($event->getItem()->getId() == Item::FIREWORKS || $event->getBlock()->getId() == Block::ITEM_FRAME_BLOCK) {
            $event->setCancelled(); //fireworks start up or picture drop xd
            return;
        }

        if (LobbySystem::getPlayerCache($player->getName())->isAddonsActivated()) {
            if ($event->getItem()->getId() == Item::DIAMOND_HOE || $event->getItem()->getId() == Item::GOLD_HOE) {
                $event->setCancelled();
                if (isset(AnimationProvider::$specialDelay[$player->getName()])) {
                    $timeToWait = AnimationProvider::$specialDelay[$player->getName()] - time();
                    if ($timeToWait > 0)
                        $player->sendActionBarMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-special-item-delay', $player->getName(), ['#time' => $timeToWait]));
                    return;
                }
                AnimationProvider::$specialDelay[$player->getName()] = time() + 3;

                $nbt = new CompoundTag("", [
                    "Pos" => new ListTag("Pos", [
                        new DoubleTag("", $player->x),
                        new DoubleTag("", $player->y + $player->getEyeHeight()),
                        new DoubleTag("", $player->z)
                    ]),
                    "Motion" => new ListTag("Motion", [
                        new DoubleTag("", $player->getDirectionVector()->x),
                        new DoubleTag("", $player->getDirectionVector()->y),
                        new DoubleTag("", $player->getDirectionVector()->z)
                    ]),
                    "Rotation" => new ListTag("Rotation", [
                        new FloatTag("", $player->yaw),
                        new FloatTag("", $player->pitch)
                    ]),
                ]);
                $nbt->setString("Addon", LobbySystem::getPlayerCache($player->getName())->getSpecial());

                $entity = Entity::createEntity("Snowball", $player->getLevelNonNull(), $nbt, $player);
                $entity->spawnToAll();
                $player->playSound('mob.spider.say', 5, 1.0, [$player]);
                if ($entity instanceof Projectile)
                    $entity->setMotion($entity->getMotion()->multiply(2));
            } else if ($event->getItem()->getId() == Item::TNT && LobbySystem::getPlayerCache($player->getName())->isAddonsActivated()) {
                if (isset(AnimationProvider::$specialDelay[$player->getName()])) {
                    $timeToWait = AnimationProvider::$specialDelay[$player->getName()] - time();
                    if ($timeToWait > 0)
                        $player->sendActionBarMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-special-item-delay', $player->getName(), ['#time' => $timeToWait]));
                    return;
                }
                AnimationProvider::$specialDelay[$player->getName()] = time() + 30;
                $event->setCancelled();
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->isAddonsActivated()) {
                            $nbt = Entity::createBaseNBT($player->asVector3());
                            $nbt->setShort("Fuse", 1);
                            $e = Entity::createEntity(PrimedTNT::NETWORK_ID, $player->getLevel(), $nbt);
                            $e->spawnToAll();
                            $player->setFlying(true);
                        }
                    }
                }
            }
        }
    }

    public function hitBlock(ProjectileHitBlockEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof Snowball) {
            $id = $entity->getLevel()->getBlock($entity->asVector3())->getId();
            $block = $event->getBlockHit()->asVector3()->floor();
            if ($id != Block::WALL_SIGN && $id != Block::STANDING_SIGN && $id != Block::LADDER && !isset(AnimationProvider::$blockReplace["{$block->x}:{$block->y}:{$block->z}"])) {
                if ($entity->namedtag->getString("Addon", "#CoVid19") == "Spiderman") {
                    AnimationProvider::$blockReplace["{$entity->x}:{$entity->y}:{$entity->z}"] = ['time' => time() + 3, 'blockId' => $entity->getLevel()->getBlock($entity->asVector3())->getId(), 'blockMeta' => $entity->getLevel()->getBlock($entity->asVector3())->getDamage()];
                    $entity->getLevel()->setBlock($entity->asVector3(), Block::get(Block::WEB));
                } else if ($entity->namedtag->getString("Addon", "#CoVid19") == "Paintball Gun") {
                    $block = $event->getBlockHit();
                    $center = $event->getBlockHit()->asPosition();
                    $center = Math::roundPosition($center);
                    $radius = 4;

                    $invRadiusX = 1 / $radius;
                    $invRadiusY = 1 / $radius;
                    $invRadiusZ = 1 / $radius;

                    $nextXn = 0;
                    $breakX = false;
                    for ($x = 0; $x <= $radius and $breakX === false; ++$x) {
                        $xn = $nextXn;
                        $nextXn = ($x + 1) * $invRadiusX;
                        $nextYn = 0;
                        $breakY = false;
                        for ($y = 0; $y <= $radius and $breakY === false; ++$y) {
                            $yn = $nextYn;
                            $nextYn = ($y + 1) * $invRadiusY;
                            $nextZn = 0;
                            for ($z = 0; $z <= $radius; ++$z) {
                                $zn = $nextZn;
                                $nextZn = ($z + 1) * $invRadiusZ;
                                $distanceSq = Math::lengthSq($xn, $yn, $zn);
                                if ($distanceSq > 1) {
                                    if ($z === 0) {
                                        if ($y === 0) {
                                            $breakX = true;
                                            $breakY = true;
                                            break;
                                        }
                                        $breakY = true;
                                        break;
                                    }
                                    break;
                                }


                                $id = $entity->getLevel()->getBlock($center->add($x, $y, $z))->getId();
                                $meta = $this->randomMeta();
                                if ($id != Block::WALL_SIGN && $id != Block::STANDING_SIGN && $id != Block::LADDER && $id != Item::AIR && $id != Block::CONCRETE && $id != Block::WEB && !isset(AnimationProvider::$blockReplace["{$center->add($x, $y, $z)->x}:{$center->add($x, $y, $z)->y}:{$center->add($x, $y, $z)->z}"])) {
                                    AnimationProvider::$blockReplace["{$center->add($x, $y, $z)->x}:{$center->add($x, $y, $z)->y}:{$center->add($x, $y, $z)->z}"] = ['time' => time() + 3, 'blockId' => $id, 'blockMeta' => $block->getDamage()];
                                    $entity->getLevel()->setBlock($center->add($x, $y, $z), Block::get(BlocK::CONCRETE, $meta));
                                }

                                $id = $entity->getLevel()->getBlock($center->add(-$x, $y, $z))->getId();
                                if ($id != Block::WALL_SIGN && $id != Block::STANDING_SIGN && $id != Block::LADDER && $id != Item::AIR && $id != Block::CONCRETE && $id != Block::WEB && !isset(AnimationProvider::$blockReplace["{$center->add(-$x, $y, $z)->x}:{$center->add(-$x, $y, $z)->y}:{$center->add(-$x, $y, $z)->z}"])) {
                                    AnimationProvider::$blockReplace["{$center->add(-$x, $y, $z)->x}:{$center->add(-$x, $y, $z)->y}:{$center->add(-$x, $y, $z)->z}"] = ['time' => time() + 3, 'blockId' => $id, 'blockMeta' => $block->getDamage()];
                                    $entity->getLevel()->setBlock($center->add(-$x, $y, $z), Block::get(BlocK::CONCRETE, $meta));
                                }

                                $id = $entity->getLevel()->getBlock($center->add(-$x, $y, $z))->getId();
                                if ($id != Block::WALL_SIGN && $id != Block::STANDING_SIGN && $id != Block::LADDER && $id != Item::AIR && $id != Block::CONCRETE && $id != Block::WEB && !isset(AnimationProvider::$blockReplace["{$center->add(-$x, $y, $z)->x}:{$center->add(-$x, $y, $z)->y}:{$center->add(-$x, $y, $z)->z}"])) {
                                    AnimationProvider::$blockReplace["{$center->add(-$x, $y, $z)->x}:{$center->add(-$x, $y, $z)->y}:{$center->add(-$x, $y, $z)->z}"] = ['time' => time() + 3, 'blockId' => $id, 'blockMeta' => $block->getDamage()];
                                    $entity->getLevel()->setBlock($center->add(-$x, $y, $z), Block::get(BlocK::CONCRETE, $meta));
                                }

                                $id = $entity->getLevel()->getBlock($center->add($x, -$y, $z))->getId();
                                if ($id != Block::WALL_SIGN && $id != Block::STANDING_SIGN && $id != Block::LADDER && $id != Item::AIR && $id != Block::CONCRETE && $id != Block::WEB && !isset(AnimationProvider::$blockReplace["{$center->add($x, -$y, $z)->x}:{$center->add($x, -$y, $z)->y}:{$center->add($x, -$y, $z)->z}"])) {
                                    AnimationProvider::$blockReplace["{$center->add($x, -$y, $z)->x}:{$center->add($x, -$y, $z)->y}:{$center->add($x, -$y, $z)->z}"] = ['time' => time() + 3, 'blockId' => $id, 'blockMeta' => $block->getDamage()];
                                    $entity->getLevel()->setBlock($center->add($x, -$y, $z), Block::get(BlocK::CONCRETE, $meta));
                                }

                                $id = $entity->getLevel()->getBlock($center->add($x, $y, -$z))->getId();
                                if ($id != Block::WALL_SIGN && $id != Block::STANDING_SIGN && $id != Block::LADDER && $id != Item::AIR && $id != Block::CONCRETE && $id != Block::WEB && !isset(AnimationProvider::$blockReplace["{$center->add($x, $y, -$z)->x}:{$center->add($x, $y, -$z)->y}:{$center->add($x, $y, -$z)->z}"])) {
                                    AnimationProvider::$blockReplace["{$center->add($x, $y, -$z)->x}:{$center->add($x, $y, -$z)->y}:{$center->add($x, $y, -$z)->z}"] = ['time' => time() + 3, 'blockId' => $id, 'blockMeta' => $block->getDamage()];
                                    $entity->getLevel()->setBlock($center->add($x, $y, -$z), Block::get(BlocK::CONCRETE, $meta));
                                }

                                $id = $entity->getLevel()->getBlock($center->add(-$x, -$y, $z))->getId();
                                if ($id != Block::WALL_SIGN && $id != Block::STANDING_SIGN && $id != Block::LADDER && $id != Item::AIR && $id != Block::CONCRETE && $id != Block::WEB && !isset(AnimationProvider::$blockReplace["{$center->add(-$x, -$y, $z)->x}:{$center->add(-$x, -$y, $z)->y}:{$center->add(-$x, -$y, $z)->z}"])) {
                                    AnimationProvider::$blockReplace["{$center->add(-$x, -$y, $z)->x}:{$center->add(-$x, -$y, $z)->y}:{$center->add(-$x, -$y, $z)->z}"] = ['time' => time() + 3, 'blockId' => $id, 'blockMeta' => $block->getDamage()];
                                    $entity->getLevel()->setBlock($center->add(-$x, -$y, $z), Block::get(BlocK::CONCRETE, $meta));
                                }

                                $id = $entity->getLevel()->getBlock($center->add($x, -$y, -$z))->getId();
                                if ($id != Block::WALL_SIGN && $id != Block::STANDING_SIGN && $id != Block::LADDER && $id != Item::AIR && $id != Block::CONCRETE && $id != Block::WEB && !isset(AnimationProvider::$blockReplace["{$center->add($x, -$y, -$z)->x}:{$center->add($x, -$y, -$z)->y}:{$center->add($x, -$y, -$z)->z}"])) {
                                    AnimationProvider::$blockReplace["{$center->add($x, -$y, -$z)->x}:{$center->add($x, -$y, -$z)->y}:{$center->add($x, -$y, -$z)->z}"] = ['time' => time() + 3, 'blockId' => $id, 'blockMeta' => $block->getDamage()];
                                    $entity->getLevel()->setBlock($center->add($x, -$y, -$z), Block::get(BlocK::CONCRETE, $meta));
                                }

                                $id = $entity->getLevel()->getBlock($center->add(-$x, $y, -$z))->getId();
                                if ($id != Block::WALL_SIGN && $id != Block::STANDING_SIGN && $id != Block::LADDER && $id != Item::AIR && $id != Block::CONCRETE && $id != Block::WEB && !isset(AnimationProvider::$blockReplace["{$center->add(-$x, $y, -$z)->x}:{$center->add(-$x, $y, -$z)->y}:{$center->add(-$x, $y, -$z)->z}"])) {
                                    AnimationProvider::$blockReplace["{$center->add(-$x, $y, -$z)->x}:{$center->add(-$x, $y, -$z)->y}:{$center->add(-$x, $y, -$z)->z}"] = ['time' => time() + 3, 'blockId' => $id, 'blockMeta' => $block->getDamage()];
                                    $entity->getLevel()->setBlock($center->add(-$x, $y, -$z), Block::get(BlocK::CONCRETE, $meta));
                                }

                                $id = $entity->getLevel()->getBlock($center->add(-$x, -$y, -$z))->getId();
                                if ($id != Block::WALL_SIGN && $id != Block::STANDING_SIGN && $id != Block::LADDER && $id != Item::AIR && $id != Block::CONCRETE && $id != Block::WEB && !isset(AnimationProvider::$blockReplace["{$center->add(-$x, -$y, -$z)->x}:{$center->add(-$x, -$y, -$z)->y}:{$center->add(-$x, -$y, -$z)->z}"])) {
                                    AnimationProvider::$blockReplace["{$center->add(-$x, -$y, -$z)->x}:{$center->add(-$x, -$y, -$z)->y}:{$center->add(-$x, -$y, -$z)->z}"] = ['time' => time() + 3, 'blockId' => $id, 'blockMeta' => $block->getDamage()];
                                    $entity->getLevel()->setBlock($center->add(-$x, -$y, -$z), Block::get(BlocK::CONCRETE, $meta));
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @return int
     */
    private function randomMeta(): int
    {
        return rand(1, 14);
    }

    public function hitEntity(ProjectileHitEntityEvent $event)
    {
        $entity = $event->getEntity();
        $opfer = $event->getEntityHit();
        if ($entity instanceof Snowball && $opfer instanceof Player) {
            if ($entity->namedtag->getString("Addon", "#CoVid19") != "Spiderman") return;
            if (($obj = LobbySystem::getPlayerCache($opfer->getName())) != null) {
                if ($obj->isAddonsActivated()) {
                    $id = $entity->getLevel()->getBlock($entity->asVector3())->getId();
                    if ($id != Block::WALL_SIGN && $id != Block::STANDING_SIGN && $id != Block::LADDER) {
                        AnimationProvider::$blockReplace["{$entity->x}:{$entity->y}:{$entity->z}"] = ['time' => time() + 3, 'blockId' => 0, 'blockMeta' => 0];
                        $entity->getLevel()->setBlock($entity->asVector3(), Block::get(Block::WEB));
                    }
                }
            }
        }
    }

    public function interactEntity(PlayerInteractEntityEvent $event)
    {
        $entity = $event->getEntity();
        $damager = $event->getPlayer();

        if ($damager instanceof Player) {
            if ($entity instanceof NPC || $entity instanceof Geometry) {
                $game = $entity->namedtag->getString("Game", "#CoVid19");
                $action = $entity->namedtag->getString("Action", "#CoVid19");
                //var_dump($action);
                if (isset(NPCProvider::getNpc()[$game])) {
                    if (LobbySystem::getConfigProvider()->getGameSpawn($game) == null) {
                        $damager->sendTitle(TextFormat::RED . "ERROR");
                        $damager->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-no-spawn', $damager->getName(), ['#game' => $game]));
                        $damager->playSound('note.bass');
                        return;
                    }
                    $damager->addEffect(new EffectInstance(Effect::getEffect(Effect::LEVITATION), 2000, 2, false));
                    $damager->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 2000, 2, false));

                    AnimationProvider::$teleportAnimation[$damager->getName()] = ['game' => NPCProvider::getNpc()[$game]['title'], 'count' => 0, 'spawn' => LobbySystem::getConfigProvider()->getGameSpawn($game), 'title' => ""];
                } else if ($action == "private_server") {
                    $damager->getServer()->getCommandMap()->dispatch($damager, "pserver");
                } else if ($action == "daily_reward") {
                    $damager->getServer()->getCommandMap()->dispatch($damager, "dailyreward");
                    $entity->playEmote(Emotes::DIAMONDS_TO_YOU);
                } else if ($action == "lotto") {
                    if (($obj = LobbySystem::getPlayerCache($damager->getName())) != null)
                        $damager->sendForm(new BuyLottoTicketForm($obj));
                    $damager->playSound('random.pling', 5, 1.0, [$damager]);
                } else if ($action == "running_cw") {
                    if (count(array_keys(LobbySystem::$runningClanWars)) != 0) {
                        $damager->sendForm(new RunningClanWarForm());
                    } else {
                        $damager->sendMessage(TextFormat::AQUA . TextFormat::BOLD . "ClanWar" . TextFormat::RESET . " " . LanguageProvider::getMessageContainer('no-clanwar-running', $damager->getName()));
                    }
                }else if($action == "replay") {
                    $damager->sendForm(new ReplayForm($damager->getName()));
                }else if($action == "invsort") {
                    $game = $entity->namedtag->getString("game", "#CoVid19");
                    if($game == "#CoVid19") {
                        $damager->sendTitle(TextFormat::RED . "ERROR");
                        $damager->playSound('note.bass');
                        return;
                    }

                    if($game == "cwtraining") {
                        BungeeAPI::transfer($damager->getName(), "OnlySortCWT");
                    }
                }else if($action == "shop"){
                    LobbySystem::getPlugin()->getServer()->dispatchCommand($damager, "shop");
                }
            }
        }
    }
}