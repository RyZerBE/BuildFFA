<?php

declare(strict_types=1);

namespace ryzerbe\buildffa;

use Exception;
use pocketmine\block\BlockFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use ryzerbe\buildffa\block\Beacon;
use ryzerbe\buildffa\command\SetupCommand;
use ryzerbe\buildffa\command\SkipCommand;
use ryzerbe\buildffa\game\GameManager;
use ryzerbe\buildffa\game\perks\PerkManager;
use ryzerbe\core\util\loader\ListenerDirectoryLoader;

class BuildFFA extends PluginBase {
    public const PREFIX = TextFormat::BOLD.TextFormat::RED."BuildFFA ".TextFormat::RESET;

    protected static BuildFFA $instance;

    public function onEnable(): void{
        self::$instance = $this;
        try{
            ListenerDirectoryLoader::load($this, $this->getFile(), __DIR__."/listener/");
        } catch(Exception $exception) {
            Server::getInstance()->getLogger()->logException($exception);
        }

        GameManager::init();
        PerkManager::getInstance();

        Server::getInstance()->getCommandMap()->registerAll("buildffa", [
            new SetupCommand(),
            new SkipCommand(),
        ]);

        BlockFactory::registerBlock(new Beacon(), true);
    }

    public static function getInstance(): BuildFFA{
        return self::$instance;
    }
}