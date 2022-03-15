<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use ryzerbe\buildffa\game\GameManager;

class SkipCommand extends Command {
    public function __construct(){
        parent::__construct("skip", "Skip current map and kit");
        $this->setPermission("game.buildffa.map.skip");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void{
        if(!$sender instanceof Player || !$this->testPermission($sender)) return;
        GameManager::$mapChangeTimer = 0;
    }
}