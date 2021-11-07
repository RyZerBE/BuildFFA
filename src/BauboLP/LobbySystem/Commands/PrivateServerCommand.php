<?php


namespace BauboLP\LobbySystem\Commands;


use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\Core\Ryzer;
use BauboLP\LobbySystem\Forms\PServerForm;
use BauboLP\LobbySystem\LobbySystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class PrivateServerCommand extends Command
{

    public function __construct()
    {
        parent::__construct('pserver',  "", "", ['']);
        $this->setPermission("lobby.ps");
        $this->setPermissionMessage(LobbySystem::Prefix.TextFormat::RED."Du hast keinen Rang mit der Fähigkeit, Private Server erstellen zu können.");
    }

    /**
     * @inheritDoc
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player) return;
        if(!$this->testPermission($sender)) return;

        $sender->sendForm(new PServerForm());
    }
}