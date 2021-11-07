<?php


namespace BauboLP\BuildFFA\commands;


use BauboLP\BuildFFA\BuildFFA;
use BauboLP\BuildFFA\provider\GameProvider;
use BauboLP\BuildFFA\provider\ItemProvider;
use baubolp\core\provider\LanguageProvider;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class ForceVotingCommand extends Command
{

    public function __construct()
    {
        parent::__construct("forcevoting", "", "", ["fv"]);
        $this->setPermission("bffa.forcevote");
        $this->setPermissionMessage(BuildFFA::Prefix.TextFormat::RED."No Permissions!");
    }

    /**
     * @inheritDoc
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player) return;
        if(!$this->testPermission($sender)) return;

        if(GameProvider::$forceDelay > time()) {
            $time = GameProvider::$forceDelay - time()." Sekunden";
            if(GameProvider::$forceDelay - time() > 60) {
                $time = (GameProvider::$forceDelay - time()) / 60;
                $time = explode(".", number_format($time, 2));
                if(isset($time[0]) && isset($time[1]))
                $time = $time[0]." Minuten, ".$time[1]." Sekunden";
                else
                    $time = $time[0]." Minuten";
            }
            $sender->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer("bffa-forcevote-delay", $sender->getName(), ["#time" => $time]));
            return;
        }

        GameProvider::setPvP(false);
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
        GameProvider::$forceDelay = time() + (60 * 5);
        $sender->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer("bffa-forcevote-successful", $sender->getName()));
    }
}