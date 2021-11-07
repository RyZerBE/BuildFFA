<?php


namespace BauboLP\LobbySystem\Tasks;


use BauboLP\CloudSigns\Provider\CloudSignProvider;
use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\AnimationProvider;
use pocketmine\block\Block;
use pocketmine\command\defaults\StatusCommand;
use pocketmine\item\Item;
use pocketmine\level\particle\AngryVillagerParticle;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\EnchantParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\HappyVillagerParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\HugeExplodeParticle;
use pocketmine\level\particle\LavaDripParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\tile\Sign;
use pocketmine\utils\Color;
use pocketmine\utils\TextFormat;

class AddonTask extends Task
{

    /** @var int  */
    private $checkDelay;

    const MAX_AVERAGE = 60; //Percent

    /**
     * @inheritDoc
     */
    public function onRun(int $currentTick)
    {
        if($this->checkDelay > time()) return;
        if((int)Server::getInstance()->getTickUsageAverage() > self::MAX_AVERAGE) {
            if(AnimationProvider::$addonBlocker == true) return;

            AnimationProvider::$addonBlocker = true;
            $this->checkDelay = time() + 30;
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendMessage(LobbySystem::Prefix.LanguageProvider::getMessageContainer('lobby-addonblocker-activated', $player->getName()));
                if(($obj = LobbySystem::getPlayerCache($player->getName())) != null)
                    $obj->setAddonsActivated(false);
            }
            return;
        }else {
           // var_dump(AnimationProvider::$addonBlocker);
            if(AnimationProvider::$addonBlocker === true) {
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    $player->sendMessage(LobbySystem::Prefix.LanguageProvider::getMessageContainer('lobby-addonblocker-deactivated', $player->getName()));
                    if(($obj = LobbySystem::getPlayerCache($player->getName())) != null)
                        $obj->setAddonsActivated(true);
                }

                $this->checkDelay = time() + 10;
                AnimationProvider::$addonBlocker = false;
            }
        }
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if(($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                if($obj->getNearSign() != null) {
                    if($obj->getNearSign()->distance($player) <= 10) {
                        $player->sendActionBarMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-sign-near-addons-deactivated', $player->getName()));
                        $obj->setAddonsActivated(false);
                    }else {
                        if (!$obj->isAddonsActivated() && !$obj->isInGame()) {
                            $obj->setNearSign(null);
                            $obj->setAddonsActivated(true);
                            $player->sendActionBarMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-addons-enabled', $player->getName()));
                        }
                    }
                }else {
                    foreach (Server::getInstance()->getDefaultLevel()->getTiles() as $tile) {
                        if($tile instanceof Sign) {
                            if($tile->distance($player->asVector3()) <= 10 && CloudSignProvider::isCloudSign($tile->getBlock())) {
                                if($obj->isAddonsActivated()) {
                                    $obj->setNearSign($tile->asVector3());
                                    $obj->setAddonsActivated(false);
                                    $player->sendActionBarMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-sign-near-addons-deactivated', $player->getName()));
                                    break;
                                }
                            }else {
                                if(!$obj->isAddonsActivated() && !$obj->isInGame()) {
                                    $obj->setAddonsActivated(true);
                                    $player->sendActionBarMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-addons-enabled', $player->getName()));
                                    break;
                                }
                            }
                        }
                    }
                }
                if($obj->isAddonsActivated()) {
                    if($obj->getParticle() != null && $obj->getParticle() != "") {
                        $particle = $obj->getParticle();
                        if($particle == "Hearts") {
                            $player->getServer()->getDefaultLevel()->addParticle(new HeartParticle($player->asVector3(), 1));
                        }else if($particle == "AngryVillager") {
                            $player->getServer()->getDefaultLevel()->addParticle(new AngryVillagerParticle($player->asVector3()));
                        }else if($particle == "HappyVillager") {
                            $player->getServer()->getDefaultLevel()->addParticle(new HappyVillagerParticle($player->asVector3()));
                        }else if($particle == "Enchant") {
                            $player->getServer()->getDefaultLevel()->addParticle(new EnchantParticle($player->asVector3(), Color::getDyeColor(Color::COLOR_DYE_LIME)));
                        }else if($particle == "Critical") {
                            $player->getServer()->getDefaultLevel()->addParticle(new CriticalParticle($player->asVector3(), 1));
                        }else if($particle == "HugeExplode") {
                            $player->getServer()->getDefaultLevel()->addParticle(new HugeExplodeParticle($player->asVector3()));
                        }else if($particle == "Lava") {
                            $player->getServer()->getDefaultLevel()->addParticle(new LavaParticle($player->asVector3()));
                        }else if($particle == "LavaDrip") {
                            $player->getServer()->getDefaultLevel()->addParticle(new LavaDripParticle($player->asVector3()));
                        }else if($particle == "Portal") {
                            $player->getServer()->getDefaultLevel()->addParticle(new PortalParticle($player->asVector3()));
                        }
                    }

                    if($obj->getFallItem() != null && $obj->getFallItem() != "") {
                        $item = $obj->getFallItem();
                        if ($item == "Beacons") {
                            $itemEntity = $player->getServer()->getDefaultLevel()->dropItem($player->asVector3(), Item::get(Item::BEACON, 0, 1));
                            AnimationProvider::$itemsToKill[$itemEntity->getId()] = ['entity' => $itemEntity, 'time' => time() + 1, 'id' => $itemEntity->getId()];
                        } elseif ($item == "Emeralds") {
                            $itemEntity = $player->getServer()->getDefaultLevel()->dropItem($player->asVector3(), Item::get(Item::EMERALD, 0, 1));
                            AnimationProvider::$itemsToKill[$itemEntity->getId()] = ['entity' => $itemEntity, 'time' => time() + 1, 'id' => $itemEntity->getId()];
                        } elseif ($item == "Redstone") {
                            $itemEntity = $player->getServer()->getDefaultLevel()->dropItem($player->asVector3(), Item::get(Item::REDSTONE, 0, 1));
                            AnimationProvider::$itemsToKill[$itemEntity->getId()] = ['entity' => $itemEntity, 'time' => time() + 1, 'id' => $itemEntity->getId()];
                        } elseif ($item == "Diamonds") {
                            $itemEntity = $player->getServer()->getDefaultLevel()->dropItem($player->asVector3(), Item::get(Item::DIAMOND, 0, 1));
                            AnimationProvider::$itemsToKill[$itemEntity->getId()] = ['entity' => $itemEntity, 'time' => time() + 1, 'id' => $itemEntity->getId()];
                        } elseif ($item == "Netherstars") {
                            $itemEntity = $player->getServer()->getDefaultLevel()->dropItem($player->asVector3(), Item::get(Item::NETHERSTAR, 0, 1));
                            AnimationProvider::$itemsToKill[$itemEntity->getId()] = ['entity' => $itemEntity, 'time' => time() + 1, 'id' => $itemEntity->getId()];
                        } elseif ($item == "Endportals") {
                            $itemEntity = $player->getServer()->getDefaultLevel()->dropItem($player->asVector3(), Item::get(Item::END_PORTAL_FRAME, 0, 1));
                            AnimationProvider::$itemsToKill[$itemEntity->getId()] = ['entity' => $itemEntity, 'time' => time() + 1, 'id' => $itemEntity->getId()];
                        } elseif ($item == "Enderpearls") {
                            $itemEntity = $player->getServer()->getDefaultLevel()->dropItem($player->asVector3(), Item::get(Item::ENDER_PEARL, 0, 1));
                            AnimationProvider::$itemsToKill[$itemEntity->getId()] = ['entity' => $itemEntity, 'time' => time() + 1, 'id' => $itemEntity->getId()];
                        } elseif ($item == "Sugar") {
                            $itemEntity = $player->getServer()->getDefaultLevel()->dropItem($player->asVector3(), Item::get(Item::SUGAR, 0, 1));
                            AnimationProvider::$itemsToKill[$itemEntity->getId()] = ['entity' => $itemEntity, 'time' => time() + 1, 'id' => $itemEntity->getId()];
                        } elseif ($item == "Cookies") {
                            $itemEntity = $player->getServer()->getDefaultLevel()->dropItem($player->asVector3(), Item::get(Item::COOKIE, 0, 1));
                            AnimationProvider::$itemsToKill[$itemEntity->getId()] = ['entity' => $itemEntity, 'time' => time() + 1, 'id' => $itemEntity->getId()];
                        } elseif ($item == "Beds") {
                            $itemEntity = $player->getServer()->getDefaultLevel()->dropItem($player->asVector3(), Item::get(Item::BED, rand(0, 12), 1));
                            AnimationProvider::$itemsToKill[$itemEntity->getId()] = ['entity' => $itemEntity, 'time' => time() + 1, 'id' => $itemEntity->getId()];
                        } elseif ($item == "Enchantment Tables") {
                            $itemEntity = $player->getServer()->getDefaultLevel()->dropItem($player->asVector3(), Item::get(Item::ENCHANTMENT_TABLE, 0, 1));
                            AnimationProvider::$itemsToKill[$itemEntity->getId()] = ['entity' => $itemEntity, 'time' => time() + 1, 'id' => $itemEntity->getId()];
                        }
                    }
                }
            }
        }
    }
}