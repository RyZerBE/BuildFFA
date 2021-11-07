<?php


namespace BauboLP\LobbySystem\Commands;


use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\LobbySystem\Forms\DailyRewardForm;
use BauboLP\LobbySystem\LobbySystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class RewardCommand extends Command
{

    public function __construct()
    {
        parent::__construct("dailyreward", "", "", []);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player) return;

        if(($obj = LobbySystem::getPlayerCache($sender->getName())) != null) {
            if($obj->getDailyCoins() == null || $obj->getDailyCoinBomb() == null || $obj->getDailyLotto() == null) {
                $sender->sendMessage(LobbySystem::Prefix.LanguageProvider::getMessageContainer('lobby-data-not-loaded', $sender->getName()));
                return;
            }
            $sender->sendForm(new DailyRewardForm($obj));
        }
    }
}