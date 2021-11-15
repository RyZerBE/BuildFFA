<?php


namespace BauboLP\BuildFFA\forms\voting;


use BauboLP\BuildFFA\BuildFFA;
use BauboLP\BuildFFA\provider\GameProvider;
use BauboLP\BuildFFA\provider\ItemProvider;
use ryzerbe\core\language\LanguageProvider;
use pocketmine\form\ModalForm;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class SkipConfirmForm extends ModalForm
{

    public function __construct(string $playerName)
    {
        parent::__construct(BuildFFA::Prefix.TextFormat::YELLOW."Skip", LanguageProvider::getMessageContainer("bffa-really-skip", $playerName, ["#skipCount" => count(GameProvider::$skip), "#skipsNeed" => count(Server::getInstance()->getOnlinePlayers())]), function (Player $player, bool $choice): void{
            if($choice) {
                if(in_array($player->getName(), GameProvider::getSkip())) {
                    $player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('already-skipped', $player->getName()));
                    return;
                }
                ItemProvider::clearAllInvs($player);
                GameProvider::addSkipPlayer($player->getName());
                $player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('successful-skipped', $player->getName()));

                if(count(GameProvider::$skip) >= count(Server::getInstance()->getOnlinePlayers()) / 2) {
                    GameProvider::$isSkipped = true;
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
                    GameProvideR::resetVotes();
                }
            }
        }, TextFormat::GREEN.TextFormat::BOLD."✔ YEAH", TextFormat::RED."✘ FAIL");
    }

}