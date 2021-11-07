<?php


namespace BauboLP\LobbySystem\Tasks;


use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\AnimationProvider;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class AnimationTask extends Task
{

    public function onRun(int $currentTick)
    {
        foreach (array_keys(AnimationProvider::$playerJoinAnimation) as $playerName) {
            if(($player = Server::getInstance()->getPlayerExact($playerName)) != null) {
                AnimationProvider::$playerJoinAnimation[$playerName]++;
                if(isset(AnimationProvider::$joinAnimation[AnimationProvider::$playerJoinAnimation[$playerName] - 1])) {
                    $player->sendTitle(AnimationProvider::$joinAnimation[AnimationProvider::$playerJoinAnimation[$playerName] - 1], TextFormat::YELLOW.'Welcome', 20, 30, 20);
                    $player->playSound("note.bass", 2, 2, [$player]);
                }else {
                    $player->playSound('mob.wither.death', 5, 1.0, [$player]);
                    AnimationProvider::removePlayerFromAnimation($playerName);
                }
            }else {
                AnimationProvider::removePlayerFromAnimation($playerName);
            }
        }

        foreach (array_keys(AnimationProvider::$teleportAnimation) as $playerName) {
            $clearGame = TextFormat::clean(AnimationProvider::$teleportAnimation[$playerName]['game']);
            $count = AnimationProvider::$teleportAnimation[$playerName]['count'];
            $count_word = strlen($clearGame);

            if($count == 0) {

                AnimationProvider::$teleportAnimation[$playerName]['count'] += 1;
                $unknow = str_repeat("_", $count_word);

                if(($player = Server::getInstance()->getPlayerExact($playerName)) != null) {
                    $player->sendTitle(TextFormat::GRAY.$unknow, TextFormat::WHITE."RyZer".TextFormat::AQUA."BE");
                    $player->playSound('jump.slime', 5, 1.0, [$player]);
                }
            }else {
                if(isset($clearGame[$count - 1])) {
                    if(($player = Server::getInstance()->getPlayerExact($playerName)) != null) {
                        AnimationProvider::$teleportAnimation[$playerName]['title'] .= $clearGame[$count - 1];
                        $title = AnimationProvider::$teleportAnimation[$playerName]['title'].TextFormat::GRAY.str_repeat("_", $count_word - ($count));
                        $player->sendTitle($title, TextFormat::WHITE."RyZer".TextFormat::AQUA."BE");
                        $player->playSound('jump.slime', 5, 1.0, [$player]);
                    }
                        AnimationProvider::$teleportAnimation[$playerName]['count'] += 1;
                }else {
                    if(($player = Server::getInstance()->getPlayerExact($playerName)) != null) {
                        $player->teleport(AnimationProvider::$teleportAnimation[$playerName]['spawn']);
                        $player->playSound('firework.blast', 5, 1.0, [$player]);
                        $player->sendTitle(AnimationProvider::$teleportAnimation[$playerName]['game'], TextFormat::WHITE."RyZer".TextFormat::AQUA."BE");
                        $player->removeAllEffects();
                    }
                    unset(AnimationProvider::$teleportAnimation[$playerName]);
                }
            }
        }
    }
}