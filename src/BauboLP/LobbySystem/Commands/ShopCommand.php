<?php


namespace BauboLP\LobbySystem\Commands;


use BauboLP\LobbySystem\Forms\shop\RankSelectForm;
use BauboLP\LobbySystem\LobbySystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ShopCommand extends Command
{

    public function __construct()
    {
        parent::__construct("shop", "", "", []);
    }

    /**
     * @inheritDoc
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player) return;

        //$sender->sendMessage(LobbySystem::Prefix.TextFormat::GOLD."Coins? -> shop.ryzer.be");
        $sender->sendForm(new RankSelectForm());
    }
}