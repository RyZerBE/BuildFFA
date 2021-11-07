<?php


namespace BauboLP\LobbySystem\Tasks;


use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\Core\Ryzer;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\ItemProvider;
use BauboLP\LobbySystem\Provider\LobbyGamesProvider;
use pocketmine\item\Item;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use BlockHorizons\Fireworks\item\Fireworks;


class LobbyGameTask extends Task
{

    /**
     * @inheritDoc
     */
    public function onRun(int $currentTick)
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                if (LobbyGamesProvider::nearGame($player)) {
                    if (!$obj->isInGame()) {
                        $obj->setAddonsActivated(false);
                        $player->playSound('note.pling', 5, 1.0, [$player]);
                        $player->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-lobbygame-near-addons-deactivated', $player->getName()));
                        $player->setFlying(false);
                        $player->setAllowFlight(false);
                        ItemProvider::clearAllInvs($player);
                        $obj->setIsInGame(true);
                    }
                } else {
                    if ($obj->isInGame()) {
                        $obj->setIsInGame(false);
                        $obj->setAddonsActivated(true);
                        $player->setAllowFlight(true);
                        $player->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-addons-enabled', $player->getName()));
                        ItemProvider::giveLobbyItems($player);
                    }
                }

                if ($obj->isInGame()) {
                  if(LobbyGamesProvider::getGoalPlayer() != null)
                  $player->sendActionBarMessage(TextFormat::YELLOW."King of Ladder: ".TextFormat::AQUA.LobbyGamesProvider::getGoalPlayer());

                    if (!$obj->isGoal()) {
                        if (LobbyGamesProvider::isGoal($player->asVector3()) && LobbyGamesProvider::getGoalPlayer() != $player->getName()) {
                            $obj->setGoal(true);
                            if(($actuellKing = Server::getInstance()->getPlayerExact((LobbyGamesProvider::getGoalPlayer()) == null ? "" : LobbyGamesProvider::getGoalPlayer())) != null) {
                               if(($ap = LobbySystem::getPlayerCache(LobbyGamesProvider::getGoalPlayer())) != null) {
                                   $ap->setGoal(false);
                                   $actuellKing->sendTitle(LanguageProvider::getMessageContainer('lobby-title-lose-king-of-leader-goal', $player->getName()), LanguageProvider::getMessageContainer('lobby-subtitle-lose-king-of-leader-goal', $player->getName()));
                               }
                            }
                            LobbyGamesProvider::setGoalPlayer($player->getName());
                            $player->sendTitle(LanguageProvider::getMessageContainer('lobby-title-king-of-leader-goal', $player->getName()), LanguageProvider::getMessageContainer('lobby-subtitle-king-of-leader-goal', $player->getName()));
                        }
                    }
                }else {
                    if(LobbyGamesProvider::getGoalPlayer() == $player->getName()) {
                        LobbyGamesProvider::setGoalPlayer(null);
                        $player->sendTitle(LanguageProvider::getMessageContainer('lobby-title-lose-king-of-leader-goal', $player->getName()), LanguageProvider::getMessageContainer('lobby-subtitle-lose-king-of-leader-goal', $player->getName()));
                        $obj->setGoal(false);
                    }
                }
            }
        }

        foreach(Server::getInstance()->getOnlinePlayers() as $player) {
            if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                if($obj->playingJumpAndRun()) {
                    $obj->updateTimer();
                    $player->setFlying(false);
                    $player->sendActionBarMessage(TextFormat::GREEN.$obj->getJumpAndRunTimeString());
                }
                if(LobbyGamesProvider::wantToStartJumpAndRun($player->asVector3()) && !$obj->playingJumpAndRun()) {
                    $obj->setPlayingJumpAndRun(true);
                    $player->teleport(LobbyGamesProvider::$jumpAndRunStartVec);
                    ItemProvider::clearAllInvs($player);

                    $quit = Item::get(Item::DYE, 1, 1)->setCustomName(TextFormat::RED."Quit");
                    $spawn = Item::get(Item::GUNPOWDER, 0, 1)->setCustomName(TextFormat::RED."Go to start");
                    $player->getInventory()->setItem(4, $spawn);
                    $player->getInventory()->setItem(8, $quit);
                    $player->playSound('note.bass', 5.0, 2.0, [$player]);
                    $player->setAllowFlight(false);
                    $player->setFlying(false);
                    foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer)
                        $player->hidePlayer($onlinePlayer);
                }else if($obj->playingJumpAndRun() && LobbyGamesProvider::finishedJumpAndRun($player)) {
                    ItemProvider::giveLobbyItems($player);
                    $player->setAllowFlight(true);
                    $obj->setPlayingJumpAndRun(false);
                    $time = $obj->getJumpAndRunTimeString();
                    $obj->resetTimer();
                    $player->sendMessage(Ryzer::PREFIX.LanguageProvider::getMessageContainer('lobby-finished-jump-and-run', $player->getName(), ['#time' => $time]));
                    $player->playSound('random.levelup', 5.0, 1.0, [$player]);
                    LobbySystem::createFirework($player->asVector3(), Fireworks::TYPE_SMALL_SPHERE, Fireworks::COLOR_YELLOW);

                    if($obj->getBestJumpAndRunTime() == "0:00") { //default value..
                        $name = $player->getName();
                        $obj->setBestJumpAndRunTime($time);
                        Ryzer::renameFloatingText($obj->getJarHolo()->getEntityId(), TextFormat::DARK_GRAY."-= ".TextFormat::AQUA."J&R ".TextFormat::DARK_GRAY."=-\n".TextFormat::GRAY."Your best time: ".TextFormat::YELLOW.$time, "", [$player]);
                      //  var_dump("DEFAULT REPLACED!");
                        Ryzer::getAsyncConnection()->execute("INSERT INTO `JumpAndRun`(`playername`, `time`) VALUES ('$name', '$time')", "Lobby", null);
                    }else {
                        $minutes = explode(":", $obj->getBestJumpAndRunTime())[0] * 60;
                        $seconds = explode(":", $obj->getBestJumpAndRunTime())[1];
                        $result = $seconds + $minutes;

                        $minutes1 = explode(":", $time)[0] * 60;
                        $seconds1 = explode(":", $time)[1];
                        $result2 = $minutes1 + $seconds1;

                        if($result2 < $result) {
                            $name = $player->getName();
                         //   var_dump("NEW TIME!");
                            Ryzer::renameFloatingText($obj->getJarHolo()->getEntityId(), TextFormat::DARK_GRAY."-= ".TextFormat::AQUA."J&R ".TextFormat::DARK_GRAY."=-\n".TextFormat::GRAY."Your best time: ".TextFormat::YELLOW.$time, "", [$player]);
                            Ryzer::getAsyncConnection()->execute("UPDATE `JumpAndRun` SET time='$time' WHERE playername='$name'", "Lobby", null);
                        }
                    }
                }
            }
        }
    }
}