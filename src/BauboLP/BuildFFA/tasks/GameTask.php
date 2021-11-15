<?php


namespace BauboLP\BuildFFA\tasks;


use BauboLP\BuildFFA\BuildFFA;
use BauboLP\BuildFFA\provider\GameProvider;
use BauboLP\BuildFFA\provider\ItemProvider;
use ryzerbe\core\language\LanguageProvider;
use BauboLP\NPCSystem\entity\NPC;
use BauboLP\NPCSystem\NPCSystem;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use xenialdan\apibossbar\BossBar;

class GameTask extends Task
{
    /**
     * @var \pocketmine\entity\utils\Bossbar
     */
    public static $BOSSBAR;
    /** @var int */
    public static $END_TIME;

    public function __construct()
    {
        self::$BOSSBAR = new BossBar();
    }

    /**
     * @inheritDoc
     */
    public function onRun(int $currentTick)
    {
        $time = GameProvider::getTimer();
        if(!GameProvider::isVoting()) {
            GameProvider::updateTimer();
            self::$BOSSBAR->setTitle(BuildFFA::Prefix.TextFormat::GRAY."Map: ".TextFormat::YELLOW.GameProvider::getMap());
            self::$BOSSBAR->setSubTitle("         ".TextFormat::DARK_GRAY."> ".TextFormat::GOLD.$time.TextFormat::DARK_GRAY." <"."        ");
            $calculate = 1;
            self::$BOSSBAR->setPercentage($calculate);
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                if (BuildFFA::$teaming)
                    $player->sendActionBarMessage(TextFormat::DARK_GREEN . "✔ " . TextFormat::BOLD.LanguageProvider::getMessageContainer("teaming-allowed", $player->getName()) .TextFormat::RESET. TextFormat::DARK_GREEN . " ✔");
                else
                    $player->sendActionBarMessage(TextFormat::DARK_RED . "✘ " . TextFormat::BOLD.LanguageProvider::getMessageContainer("teaming-not-allowed", $player->getName()) . TextFormat::RESET. TextFormat::DARK_RED . " ✘");
            }
        }else {
            self::$BOSSBAR->setPercentage(1);
            self::$BOSSBAR->setTitle(BuildFFA::Prefix.TextFormat::DARK_GRAY."> ".TextFormat::GREEN.TextFormat::YELLOW."Voting");
            self::$BOSSBAR->setSubTitle(TextFormat::GREEN."You are ready? /skip");
        }

        if ($time == "15:00") {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendMessage(BuildFFA::Prefix . LanguageProvider::getMessageContainer('vote-in-15-minutes', $player->getName()));
                $player->playSound('random.bass', 5, 2.0, [$player]);
            }
        } elseif ($time == "05:00") {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendMessage(BuildFFA::Prefix . LanguageProvider::getMessageContainer('vote-in-5-minutes', $player->getName()));
                $player->playSound('random.bass', 5, 2.0, [$player]);
            }
        } elseif ($time == "01:00") {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendMessage(BuildFFA::Prefix . LanguageProvider::getMessageContainer('vote-in-1-minutes', $player->getName()));
                $player->playSound('random.bass', 5, 2.0, [$player]);
            }
        } elseif ($time == "00:30") {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendMessage(BuildFFA::Prefix . LanguageProvider::getMessageContainer('vote-in-30-seconds', $player->getName()));
                $player->playSound('random.bass', 5, 2.0, [$player]);
            }
        }elseif ($time == "00:15") {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendMessage(BuildFFA::Prefix . LanguageProvider::getMessageContainer('vote-in-15-seconds', $player->getName()));
                $player->playSound('random.bass', 5, 2.0, [$player]);
            }
        }elseif ($time == "00:05") {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendMessage(BuildFFA::Prefix . LanguageProvider::getMessageContainer('vote-in-5-seconds', $player->getName()) . "\n" . BuildFFA::Prefix.LanguageProvider::getMessageContainer('pvp-deactivated', $player->getName()));
                $player->playSound('random.bass', 5, 2.0, [$player]);
            }
            GameProvider::setPvP(false);
        }else if($time == "00:00" && !GameProvider::isVoting()) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendMessage(BuildFFA::Prefix . LanguageProvider::getMessageContainer('vote-begin', $player->getName()) . "\n" . BuildFFA::Prefix.LanguageProvider::getMessageContainer('voting-end-in-30-seconds', $player->getName()));
            }
            GameProvider::setVoting(true);
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                ItemProvider::giveVoteItems($player);
                $player->sendTitle(TextFormat::GREEN."Voting-Phase", LanguageProvider::getMessageContainer('use-skip-when-ready', $player->getName()));
                $player->teleport(Server::getInstance()->getLevelByName(GameProvider::VOTE_AREA)->getSafeSpawn());
                $player->playSound('random.levelup', 5, 1.0, [$player]);
            }
            BuildFFA::getPlugin()->getScheduler()->scheduleRepeatingTask(new class extends Task{

                private $timer = 0;

                public function onRun(int $currentTick)
                {
                    $this->timer++;
                    if(GameProvider::$isSkipped) {
                        GameProvider::$isSkipped = false;
                        BuildFFA::getPlugin()->getScheduler()->cancelTask($this->getTaskId());
                        return;
                    }
                    if($this->timer == 15) {
                        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                            $player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('voting-end-in-15-seconds', $player->getName()));
                        }
                    }elseif($this->timer == 25) {
                        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                            $player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('voting-end-in-5-seconds', $player->getName()));
                        }
                    }elseif($this->timer == 26) {
                        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                            $player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('voting-end-in-4-seconds', $player->getName()));
                        }
                    }elseif($this->timer == 27) {
                        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                            $player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('voting-end-in-3-seconds', $player->getName()));
                        }
                    }elseif($this->timer == 28) {
                        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                            $player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('voting-end-in-2-seconds', $player->getName()));
                        }
                    }elseif($this->timer == 29) {
                        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                            $player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('voting-end-in-1-seconds', $player->getName()));
                        }
                    }elseif($this->timer == 30) {
                        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                            if(($obj = GameProvider::getBuildFFAPlayer($player->getName())) != null) {
                                if($obj->getVoteMap() != null) {
                                    if(isset(GameProvider::$maps[$obj->getVoteMap()]))
                                        GameProvider::$maps[$obj->getVoteMap()]['votes'] = GameProvider::$maps[$obj->getVoteMap()]['votes'] + 1;
                                }

                                if($obj->getVoteKit() != null) {
                                    if(isset(GameProvider::$kits[$obj->getVoteKit()]))
                                        GameProvider::$kits[$obj->getVoteKit()]['votes'] = GameProvider::$kits[$obj->getVoteKit()]['votes'] + 1;
                                }
                            }
                        }

                        $votedArena = GameProvider::getVotedArena();
                        $votedKit = GameProvider::getVotedKit();

                        GameProvider::setMap($votedArena);
                        GameProvider::setKit($votedKit);

                        Server::getInstance()->getLevelByName(GameProvider::getMap())->setTime(6000);
                        Server::getInstance()->getLevelByName(GameProvider::getMap())->stopTime();

                        GameProvider::$time = null;

                        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                            $player->sendTitle(TextFormat::DARK_RED."Voting END", LanguageProvider::getMessageContainer('tp-now', $player->getName()));
                            $player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('voting-end-game-start', $player->getName(),  ['#kit' => ItemProvider::convertKitIndexToString($votedKit), '#map' => $votedArena]));
                            if(($obj = GameProvider::getBuildFFAPlayer($player->getName())) != null) {
                                $obj->teleportToSpawn();
                                $obj->giveItems();
                                $obj->setVoteMap(null);
                                $obj->setVoteKit(null);
                                $player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('voting-end-teleport', $player->getName()));
                            }
                        }
                        Server::getInstance()->getLevelByName(GameProvider::getMap())->addSound(new EndermanTeleportSound(Server::getInstance()->getLevelByName(GameProvider::getMap())->getSafeSpawn()));
                        GameProvider::setVoting(false);
                        GameProvider::setPvP(true);

                        GameProvider::clearSkips();
                        GameProvider::resetVotes();
                        BuildFFA::getPlugin()->getScheduler()->cancelTask($this->getTaskId());
                    }
                }
            }, 20);
        }

        if(!GameProvider::isVoting()) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                if(($obj = GameProvider::getBuildFFAPlayer($player->getName())) != null) {
                  if($player->getY() < 5) {
                      $ev = new EntityDamageEvent($player, EntityDamageEvent::CAUSE_VOID, 0.0);
                      $ev->call();
                      return;
                  }
                    $sort = $obj->getInvSorts()[GameProvider::getKit()];
                    if($obj->getCooldowns()['ep'] != null) {
                        if($obj->getCooldowns()['ep'] - time() <= 0) {
                            $obj->setCooldown('ep', null);
                            $obj->getPlayer()->getInventory()->setItem($sort["ep"], Item::get(Item::ENDER_PEARL, 0, 1)->setCustomName(TextFormat::GREEN."EnderPearl"));
                        }else {
                            $obj->getPlayer()->getInventory()->setItem($sort["ep"], Item::get(Item::ENDER_EYE, 0, (int)$obj->getCooldowns()['ep'] - time())->setCustomName(TextFormat::RED . "EnderPearl"));
                        }
                    }

                    if($obj->getCooldowns()['rp'] != null) {
                        if($obj->getCooldowns()['rp'] - time() <= 0) {
                            $obj->setCooldown('rp', null);
                            $obj->getPlayer()->getInventory()->setItem($sort["rp"], Item::get(Item::BLAZE_ROD, 0, 1)->setCustomName(TextFormat::GREEN."Rettungsplattform"));
                        }else {
                            $obj->getPlayer()->getInventory()->setItem($sort["rp"], Item::get(Item::STICK, 0, (int)$obj->getCooldowns()['rp'] - time())->setCustomName(TextFormat::RED . "Rettungsplattform"));
                        }
                    }
                }
            }
        }else {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                if (($obj = GameProvider::getBuildFFAPlayer($player->getName())) != null) {
                    $obj->setCooldown('ep', null);
                    $obj->setCooldown('rp', null);
                }
            }
        }
    }
}