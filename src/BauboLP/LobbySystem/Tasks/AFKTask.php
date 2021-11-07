<?php


namespace BauboLP\LobbySystem\Tasks;


use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\AnimationProvider;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class AFKTask extends Task
{

    /**
     * @inheritDoc
     */
    public function onRun(int $currentTick)
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                if ($obj->getLastPos() != $player->asVector3()) {
                    $obj->setLastPos($player->asVector3());
                    $obj->setPosChecks(0);
                } else {
                    if ($obj->getPosChecks() < 300) {
                        $obj->setPosChecks($obj->getPosChecks() + 1);
                        if ($obj->getPosChecks() == 300) {
                           // $pos = new Vector3(-15.5, 126, 35.5);
                            //$player->teleport($pos);
                           // $player->sendMessage(LobbySystem::Prefix.LanguageProvider::getMessageContainer('you-now-afk', $player->getName()));
                            AnimationProvider::$playerAFkAnimation[$player->getName()] = 0;
                           // $obj->setLastPos($pos);
                        }
                    } else {
                        if (isset(AnimationProvider::$playerAFkAnimation[$player->getName()])) {
                            if (isset(AnimationProvider::$afkAnimation[AnimationProvider::$playerAFkAnimation[$player->getName()]])) {
                                $player->sendTitle(AnimationProvider::$afkAnimation[AnimationProvider::$playerAFkAnimation[$player->getName()]], TextFormat::GOLD."shop.ryzer.be");
                                AnimationProvider::$playerAFkAnimation[$player->getName()]++;
                            } else {
                                AnimationProvider::$playerAFkAnimation[$player->getName()] = 0;
                            }
                        }
                    }
                }
            }
        }
    }
}