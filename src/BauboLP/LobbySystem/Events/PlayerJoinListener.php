<?php


namespace BauboLP\LobbySystem\Events;


use BauboLP\Cloud\Bungee\BungeeAPI;
use BauboLP\Core\Provider\GameTimeProvider;
use BauboLP\Core\Provider\MySQLProvider;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\ItemProvider;
use BauboLP\LobbySystem\Provider\LobbyGamesProvider;
use BauboLP\LobbySystem\Provider\NPCProvider;
use BauboLP\LobbySystem\Tasks\LoadDataAsyncTask;
use BauboLP\LobbySystem\Utils\LobbyPlayer;
use BauboLP\LobbySystem\Utils\LPlayer;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\Elytra;
use pocketmine\Server;

class PlayerJoinListener implements Listener
{

    public function onJoin(PlayerJoinEvent $event)
    {
        $event->setJoinMessage("");
        $event->getPlayer()->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn()->add(0, 1));
        $event->getPlayer()->setXpLevel(LobbySystem::YEAR);
        $event->getPlayer()->setAllowFlight(true);
        $event->getPlayer()->setMaxHealth(6);
        //$event->getPlayer()->setFlying(true);
        LobbySystem::$players[$event->getPlayer()->getName()] = new LobbyPlayer($event->getPlayer());
        Server::getInstance()->getAsyncPool()->submitTask(new LoadDataAsyncTask($event->getPlayer()->getName(), MySQLProvider::getMySQLData()));
        if(isset(LobbySystem::$players[$event->getPlayer()->getName()])) { //
            $obj = LobbySystem::$players[$event->getPlayer()->getName()];
            if($obj->willAnimation()) {
                $obj->sendJoinAnimation();
            }
            $obj->updateScoreboard();
        }
        NPCProvider::spawnNPCS($event->getPlayer());
        GameTimeProvider::loadTop5Hologram($event->getPlayer()->getName());
        LobbyGamesProvider::spawnTop5OfJumpAndRun($event->getPlayer()->getName());
        LobbySystem::$bossBar->addPlayer($event->getPlayer());
        LobbySystem::$bossBar->showTo([$event->getPlayer()]);
        $event->getPlayer()->setViewDistance(LobbySystem::VIEW_DISTANCE);
        foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
            if(($obj = LobbySystem::getPlayerCache($onlinePlayer->getName())) != null) {
                if($obj->playingJumpAndRun())
                    $onlinePlayer->hidePlayer($event->getPlayer());
            }
        }
    }

    public function Quit(PlayerQuitEvent $event)
    {
        $event->setQuitMessage("");
        LobbySystem::$bossBar->removePlayer($event->getPlayer());
        unset(LobbySystem::$players[$event->getPlayer()->getName()]);
    }
}