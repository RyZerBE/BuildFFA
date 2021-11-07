<?php


namespace BauboLP\LobbySystem\Forms;


use BauboLP\Cloud\Bungee\BungeeAPI;
use BauboLP\LobbySystem\LobbySystem;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class RunningClanWarForm extends MenuForm
{

    public function __construct()
    {
        $options = [];
        $servers = [];
        foreach (array_keys(LobbySystem::$runningClanWars) as $server) {
            $servers[] = $server;
            $data = LobbySystem::$runningClanWars[$server];
            $elo = ($data[0] == true) ? TextFormat::GREEN."ON" : TextFormat::RED."OFF";
            $options[] = new MenuOption(TextFormat::YELLOW.$data[2].TextFormat::RED.TextFormat::BOLD." VS ".TextFormat::RESET.TextFormat::YELLOW.$data[4]."\n"
                                            .TextFormat::YELLOW."Map: ".TextFormat::AQUA.$data[1].TextFormat::YELLOW."    Elo: ".$elo);
        }
        parent::__construct(TextFormat::AQUA.TextFormat::BOLD."ClanWar ".TextFormat::GREEN."Spectate", "", $options, function (Player $player, int $selectedOption) use ($servers): void{
               $server = $servers[$selectedOption];
               BungeeAPI::transfer($player->getName(), $server);
        });
    }
}