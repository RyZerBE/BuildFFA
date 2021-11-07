<?php


namespace BauboLP\LobbySystem\Forms;


use BauboLP\Cloud\Bungee\BungeeAPI;
use BauboLP\Cloud\CloudBridge;
use BauboLP\Cloud\Provider\CloudProvider;
use BauboLP\LobbySystem\LobbySystem;

use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class LobbySwitcherForm extends MenuForm
{

    public function __construct()
    {
        $options = [];
        $lobbys = [];
        foreach (CloudBridge::getCloudProvider()->getRunningServersByGroup("Lobby") as $lobby) {
            $lobbys[] = $lobby;
        }

        foreach ($lobbys as $lobby) {
            $online = (isset(LobbySystem::$lobbys[$lobby]) == true) ? LobbySystem::$lobbys[$lobby] : 0;
            if($lobby == CloudProvider::getServer()) {
                $online = count(Server::getInstance()->getOnlinePlayers());
                $options[] = new MenuOption(TextFormat::DARK_AQUA.$lobby.TextFormat::GRAY."(".TextFormat::RED."YOUR".TextFormat::GRAY.")"."\n".TextFormat::GOLD.$online.TextFormat::DARK_GRAY."/".TextFormat::RED."25");
            }else {
                $options[] = new MenuOption(TextFormat::AQUA.$lobby."\n".TextFormat::GOLD.$online.TextFormat::DARK_GRAY."/".TextFormat::RED."25");
            }
        }
        $onSubmit = function (Player $player, int $selectedOption) use ($lobbys): void {
             $lobby = $lobbys[$selectedOption];
             BungeeAPI::transfer($player->getName(), $lobby);
        };
        parent::__construct(LobbySystem::Prefix."Lobby", "", $options, $onSubmit);
    }
}