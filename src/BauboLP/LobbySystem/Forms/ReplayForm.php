<?php


namespace BauboLP\LobbySystem\Forms;


use BauboLP\Cloud\CloudBridge;
use BauboLP\Cloud\Packets\CreatePrivateServerPacket;
use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\LobbySystem\LobbySystem;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ReplayForm extends MenuForm
{


    public function __construct(string $playerName)
    {
        $options = [new MenuOption(TextFormat::RED."ReplayServer"."\n".TextFormat::GREEN."Click to start")];

        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA.'ReplayServer', LanguageProvider::getMessageContainer('form-text-replay', $playerName), $options, function (Player $player, int $selectedOption): void{
            if(!$player->hasPermission("lobby.replay")) {
                $player->sendMessage(LobbySystem::Prefix.TextFormat::RED."No Permissions :/");
                return;
            }
            $packet = new CreatePrivateServerPacket();
            $packet->group = "ReplayServer";
            $packet->playerName = $player->getName();
            CloudBridge::getInstance()->getClient()->getPacketHandler()->writePacket($packet);
            $player->sendMessage(LobbySystem::Prefix."Anfrage zur Cloud gesendet.");
        });
    }
}