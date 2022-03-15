<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\command;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use ryzerbe\buildffa\game\setup\SetupManager;
use ryzerbe\core\player\PMMPPlayer;
use function is_file;
use function scandir;

class SetupCommand extends Command {
    public function __construct(){
        parent::__construct("setup", "Setup Command");
        $this->setPermission("game.setup");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void{
        if(!$sender instanceof Player || !$this->testPermission($sender)) return;
        $form = new SimpleForm(function(PMMPPlayer $player, mixed $data): void {
            if($data === null) return;
            $server = Server::getInstance();
            $server->loadLevel($data);
            $player->teleport($server->getLevelByName($data)->getSpawnLocation());

            $slot = 0;
            foreach(SetupManager::getItems() as $item) {
                $item->giveToPlayer($player, $slot++);
            }
        });

        $server = Server::getInstance();
        $path = $server->getDataPath()."/worlds/";
        $defaultWorld = $server->getDefaultLevel()->getFolderName();
        foreach(scandir($path) as $world) {
            if(!is_file($path.$world."/level.dat")) continue;
            if($world === $defaultWorld) {
                continue;
            }
            $form->addButton($world, 0, "", $world);
        }
        $form->sendToPlayer($sender);
    }
}