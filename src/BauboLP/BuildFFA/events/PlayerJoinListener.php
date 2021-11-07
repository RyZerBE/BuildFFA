<?php


namespace BauboLP\BuildFFA\events;


use BauboLP\BuildFFA\BuildFFA;
use BauboLP\BuildFFA\provider\GameProvider;
use BauboLP\BuildFFA\provider\ItemProvider;
use BauboLP\BuildFFA\tasks\GameTask;
use baubolp\core\provider\AsyncExecutor;
use baubolp\core\provider\LanguageProvider;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class PlayerJoinListener implements Listener
{

    public function join(PlayerJoinEvent $event)
    {
        $event->setJoinMessage('');
        $player = GameProvider::createPlayer($event->getPlayer());
        if(GameProvider::isVoting()) {
            ItemProvider::giveVoteItems($event->getPlayer());
            $event->getPlayer()->sendTitle(TextFormat::GREEN."Voting-Phase", LanguageProvider::getMessageContainer('use-skip-when-ready', $event->getPlayer()->getName()));
            $event->getPlayer()->teleport(Server::getInstance()->getLevelByName(GameProvider::VOTE_AREA)->getSafeSpawn());
            $event->getPlayer()->playSound('random.levelup', 5, 1.0, [$event->getPlayer()]);
        }else {
            $player->teleportToSpawn();
            GameTask::$BOSSBAR->addPlayer($event->getPlayer());
            GameTask::$BOSSBAR->showToAll();

            $playerName = $event->getPlayer()->getName();
            $defSort = base64_encode(zlib_encode(serialize($player->getInvSorts()), ZLIB_ENCODING_DEFLATE));
            AsyncExecutor::submitMySQLAsyncTask("BuildFFA", function (\mysqli $mysqli) use ($playerName, $defSort) {
                $res = $mysqli->query("SELECT * FROM inventories WHERE playername='$playerName'");
                if($res->num_rows > 0) {
                    while($data = $res->fetch_assoc())
                        return $data["sort"];
                }else {
                    $mysqli->query("INSERT INTO inventories(`playername`, `sort`) VALUES ('$playerName', '$defSort')");
                }
                return null;
            }, function (Server $server, $result) use ($playerName) {
                if($result === null) return;

                $sortArray = (array) unserialize(zlib_decode(base64_decode($result)));
                if(($obj = GameProvider::getBuildFFAPlayer($playerName)) != null) {
                    $obj->setInvSorts($sortArray);
                    $obj->giveItems();
                    $obj->getPlayer()->playSound('random.levelup', 5.0, 1.0, [$obj->getPlayer()]);
                }
            });
        }
        $player->updateScoreboard();
    }

    public function quit(PlayerQuitEvent $event)
    {
        $event->setQuitMessage("");
        $playerName = $event->getPlayer()->getName();
        if(($obj = GameProvider::getBuildFFAPlayer($playerName)) != null) {
            $sort = base64_encode(zlib_encode(serialize($obj->getInvSorts()), ZLIB_ENCODING_DEFLATE));
            AsyncExecutor::submitMySQLAsyncTask("BuildFFA", function (\mysqli $mysqli) use ($playerName, $sort) {
                $mysqli->query("UPDATE `inventories` SET sort='$sort' WHERE playername='$playerName'");
            });
        }
        GameProvider::removePlayer($event->getPlayer());
    }
}