<?php

namespace BauboLP\BuildFFA\commands;

use BauboLP\BuildFFA\forms\voting\SkipConfirmForm;
use BauboLP\BuildFFA\provider\GameProvider;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;
use pocketmine\Player;

class SkipCommand extends Command {

    public function __construct(){
        parent::__construct("skip", "skip voting", "", []);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender instanceof Player) return;
        if(!GameProvider::isVoting()) return;

        $sender->sendForm(new SkipConfirmForm($sender->getName()));
    }
}