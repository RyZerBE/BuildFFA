<?php


namespace BauboLP\BuildFFA\commands;


use BauboLP\BuildFFA\BuildFFA;
use BauboLP\BuildFFA\forms\invsort\InvSortMenu;
use BauboLP\BuildFFA\provider\GameProvider;
use ryzerbe\core\language\LanguageProvider;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class InvSortCommand extends Command
{

    public function __construct()
    {
        parent::__construct('save', "", "", ['inv', 'invsort']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player) return;
        if ($sender->distance(Server::getInstance()->getLevelByName(GameProvider::getMap())->getSafeSpawn()) > 8){
            $sender->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('not-in-spawn', $sender->getName()));
            return;
        }
        if(($obj = GameProvider::getBuildFFAPlayer($sender->getName())) != null)
        InvSortMenu::loadSort(GameProvider::getKit(), $obj);
    }
}