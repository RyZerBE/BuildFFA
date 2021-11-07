<?php


namespace BauboLP\LobbySystem\Forms;


use BauboLP\Cloud\CloudBridge;
use BauboLP\Cloud\Packets\CreatePrivateServerPacket;
use BauboLP\LobbySystem\LobbySystem;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class PServerForm extends MenuForm
{

    public function __construct()
    {
        $groups = [];
        foreach (CloudBridge::getCloudProvider()->getGroups() as $group) {
            if(CloudBridge::getCloudProvider()->canGroupBePrivate($group)) {
                if($group != "ReplayServer")
                $groups[] = $group;
            }
        }
        $options = [];

        foreach ($groups as $group) {
            $options[] = new MenuOption(TextFormat::AQUA.$group."\n".TextFormat::YELLOW."Click to create");
        }

        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA.'Private Server', "", $options, function (Player $player, int $selectedOption) use($groups): void{
           $group = $groups[$selectedOption];

           if(!CloudBridge::getCloudProvider()->existGroup($group)) return;

           $packet = new CreatePrivateServerPacket();
           $packet->group = $group;
           $packet->playerName = $player->getName();
           CloudBridge::getInstance()->getClient()->getPacketHandler()->writePacket($packet);
           $player->sendMessage(LobbySystem::Prefix."Anfrage zur Cloud gesendet.");
        });
    }
}