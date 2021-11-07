<?php


namespace BauboLP\BuildFFA\events;


use BauboLP\BuildFFA\BuildFFA;
use BauboLP\BuildFFA\provider\GameProvider;
use BauboLP\BW\API\GameAPI;
use baubolp\core\provider\LanguageProvider;
use baubolp\core\player\CorePlayer;
use BauboLP\NPCSystem\entity\NPC;
use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class DamageListener implements Listener
{

    public function damage(EntityDamageEvent $event)
    {
        if($event instanceof EntityDamageByEntityEvent) {
            $entity = $event->getEntity();
            $killer = $event->getDamager();

            if($entity instanceof NPC && $killer instanceof Player) {
                $killer->getServer()->dispatchCommand($killer, "inv");
                $event->setCancelled();
                return;
            }
            if ($entity->distance(Server::getInstance()->getLevelByName(GameProvider::getMap())->getSafeSpawn()) <= 8
            || $killer ->distance(Server::getInstance()->getLevelByName(GameProvider::getMap())->getSafeSpawn()) <= 8) {
                $event->setCancelled();
                return;
            }

            if(!GameProvider::isPvP()) {
                $event->setCancelled();
                return;
            }
            if($entity instanceof Player && $killer instanceof Player && !$event->isCancelled()) {
                if ($killer instanceof CorePlayer) {
                    $killerName = $killer->getName();
                    if (($obj = GameProvider::getBuildFFAPlayer($entity->getName())) != null && ($kobj = GameProvider::getBuildFFAPlayer($killer->getName())) != null) {
                        $obj->setKiller($killer->getName());
                        if ($obj->getCombo() > 0)
                            $obj->resetCombo();
                        $kobj->addCombo();
                        $combo = $kobj->getCombo();
                        $pos = new Position((float)$entity->getX(), (float)$entity->getY() + 1, (float)$entity->getZ(), $killer->getLevel());
                        if ($combo <= 3) {
                            $itemEntity = $killer->dropItemForPlayer($pos, Item::get(Item::QUARTZ));
                            $itemEntity->setNameTag(TextFormat::GREEN . $combo . "x");
                            $itemEntity->setNameTagAlwaysVisible();
                            GameProvider::$removeItems[$itemEntity->getId()] = time() + 0.5;
                            $itemEntity = $killer->dropItemForPlayer($pos, Item::get(Item::QUARTZ));
                            $itemEntity->setNameTag(TextFormat::GREEN . $combo . "x");
                            $itemEntity->setNameTagAlwaysVisible();
                            GameProvider::$removeItems[$itemEntity->getId()] = time() + 0.5;
                        } else if ($combo >= 3 && $combo < 6) {
                            $itemEntity = $killer->dropItemForPlayer($pos, Item::get(Item::GLOWSTONE_DUST));
                            $itemEntity->setNameTag(TextFormat::YELLOW . $combo . "x");
                            $itemEntity->setNameTagAlwaysVisible();
                            $itemEntity->spawnTo($killer);
                            GameProvider::$removeItems[$itemEntity->getId()] = time() + 0.5;
                            $itemEntity = $killer->dropItemForPlayer($pos, Item::get(Item::GLOWSTONE_DUST));
                            $itemEntity->setNameTag(TextFormat::YELLOW . $combo . "x");
                            $itemEntity->setNameTagAlwaysVisible();
                            GameProvider::$removeItems[$itemEntity->getId()] = time() + 0.5;
                        } else if ($combo >= 6 && $combo < 9) {
                            $itemEntity = $killer->dropItemForPlayer($pos, Item::get(Item::EMERALD));
                            $itemEntity->setNameTag(TextFormat::RED . $combo . "x");
                            $itemEntity->setNameTagAlwaysVisible();
                            GameProvider::$removeItems[$itemEntity->getId()] = time() + 0.5;
                            $itemEntity = $killer->dropItemForPlayer($pos, Item::get(Item::EMERALD));
                            $itemEntity->setNameTag(TextFormat::RED . $combo . "x");
                            $itemEntity->setNameTagAlwaysVisible();
                            GameProvider::$removeItems[$itemEntity->getId()] = time() + 0.5;
                        } else if ($combo >= 9) {
                            $itemEntity = $killer->dropItemForPlayer($pos, Item::get(Item::REDSTONE_DUST));
                            $itemEntity->setNameTag(TextFormat::RED . $combo . "x");
                            $itemEntity->setNameTagAlwaysVisible();
                            GameProvider::$removeItems[$itemEntity->getId()] = time() + 0.5;
                            $itemEntity = $killer->dropItemForPlayer($pos, Item::get(Item::REDSTONE_DUST));
                            $itemEntity->setNameTag(TextFormat::RED . $combo . "x");
                            $itemEntity->setNameTagAlwaysVisible();
                            GameProvider::$removeItems[$itemEntity->getId()] = time() + 0.5;
                        }

                        if ($entity->getHealth() - $event->getFinalDamage() <= 0) {
                            $event->setCancelled();
                            foreach (GameProvider::getPlacedBlocks() as $block) {
                                if (GameProvider::$placedBlocks[$block['stringPos']]['player'] == $obj->getPlayer()->getName())
                                    GameProvider::$placedBlocks[$block['stringPos']]['player'] = "#CoVid19";
                            }
                            $obj->setCooldown('ep', null);
                            $obj->setCooldown('rp', null);
                            $obj->teleportToSpawn();
                            $obj->giveItems();
                            $entity->sendMessage(BuildFFA::Prefix . LanguageProvider::getMessageContainer('bffa-killed-by-player', $entity->getName(), ['#killer' => $kobj->getName()]));

                            foreach($entity->getLevel()->getEntities() as $__entity) {
                                if(!$__entity instanceof EnderPearl && !$__entity instanceof \baubolp\core\entity\EnderPearl) continue;
                                if($__entity?->getOwningEntityId() === $entity->getId()) $__entity->flagForDespawn();
                            }

                            $obj->resetKiller();
                            $obj->addDeath();
                            $kobj->addKill();
                            $kobj->setKillStreak($kobj->getKillStreak() + 1);
                            $kobj->resetCombo();
                            $obj->resetCombo();
                            $obj->updateScoreboard();
                            $kobj->updateScoreboard();
                            $obj->getPlayer()->setHealth(20);
                            $kobj->getPlayer()->setHealth(20);
                            $killer->playSound('random.levelup', 5, 1.0, [$kobj->getPlayer()]);
                            $killer->sendMessage(BuildFFA::Prefix . LanguageProvider::getMessageContainer('bffa-killed-player', $killer->getName(), ['#playername' => $obj->getName()]));
                        }
                    }
                }
            }else if($killer instanceof PrimedTNT) {
                $event->setCancelled();
            }
            return;
        }

        $cause = $event->getCause();
        $entity = $event->getEntity();

        if(!$entity instanceof Player) {
            $entity->kill();
            return;
        }

        if($cause === EntityDamageEvent::CAUSE_SUFFOCATION || $cause === EntityDamageEvent::CAUSE_DROWNING || $cause === EntityDamageEvent::CAUSE_FIRE || $cause === EntityDamageEvent::CAUSE_FALL || $cause === EntityDamageEvent::CAUSE_FIRE_TICK || $cause === EntityDamageEvent::CAUSE_ENTITY_EXPLOSION) {
            $event->setCancelled();
        }else if($cause === EntityDamageEvent::CAUSE_VOID) {
            $event->setCancelled();
            if(GameProvider::isVoting()) {
                $event->setCancelled();
                $entity->teleport($entity->getLevel()->getSafeSpawn());
                return;
            }
            if(($obj = GameProvider::getBuildFFAPlayer($entity->getName())) != null) {
                if($obj->getKiller() != $entity->getName()) {
                    if(($kobj = GameProvider::getBuildFFAPlayer($obj->getKiller())) != null) {
                      if($kobj->getPlayer() != null) {
                          $kobj->addKill();
                          $kobj->getPlayer()->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('bffa-killed-player', $kobj->getPlayer()->getName(), ['#playername' => $obj->getName()]));
                          $kobj->getPlayer()->playSound('random.levelup', 5, 1.0, [$kobj->getPlayer()]);
                          $kobj->setKillStreak($kobj->getKillStreak() + 1);
                          $kobj->updateScoreboard();
                          $kobj->getPlayer()->setHealth(20);
                      }
                        $entity->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('bffa-killed-by-player', $entity->getName(), ['#killer' => $kobj->getName()]));
                    }
                }

                foreach($entity->getLevel()->getEntities() as $__entity) {
                    if(!$__entity instanceof EnderPearl && !$__entity instanceof \baubolp\core\entity\EnderPearl) continue;
                    if($__entity?->getOwningEntityId() === $entity->getId()) $__entity->flagForDespawn();
                }
                $obj->setCooldown('ep', null);
                $obj->setCooldown('rp', null);
                $obj->resetKillStreak();
                $obj->teleportToSpawn();
                $obj->giveItems();
                $obj->resetKiller();
                $obj->addDeath();
                $obj->updateScoreboard();
                $obj->getPlayer()->setHealth(20);
            }
        }
    }
}