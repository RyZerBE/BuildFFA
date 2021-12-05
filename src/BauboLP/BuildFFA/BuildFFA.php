<?php


namespace BauboLP\BuildFFA;


use BauboLP\BuildFFA\commands\ForceVotingCommand;
use BauboLP\BuildFFA\commands\InvSortCommand;
use BauboLP\BuildFFA\commands\SkipCommand;
use BauboLP\BuildFFA\events\BlockBreakListener;
use BauboLP\BuildFFA\events\BlockPlaceListener;
use BauboLP\BuildFFA\events\BlockUpdateListener;
use BauboLP\BuildFFA\events\DamageListener;
use BauboLP\BuildFFA\events\DropItemListener;
use BauboLP\BuildFFA\events\FeedListener;
use BauboLP\BuildFFA\events\InteractListener;
use BauboLP\BuildFFA\events\InvCloseListener;
use BauboLP\BuildFFA\events\InvTransactionListener;
use BauboLP\BuildFFA\events\PlayerJoinListener;
use BauboLP\BuildFFA\events\ProjectileHitBlockListener;
use BauboLP\BuildFFA\events\ProjectileShootListener;
use BauboLP\BuildFFA\tasks\AnimationTask;
use BauboLP\BuildFFA\tasks\DelayTask;
use BauboLP\BuildFFA\tasks\GameTask;
use ryzerbe\core\provider\VIPJoinProvider;
use ryzerbe\core\util\async\AsyncExecutor;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class BuildFFA extends PluginBase
{

    const Prefix = TextFormat::GOLD.TextFormat::BOLD."BuildFFA ".TextFormat::RESET;
    /** @var \BauboLP\BuildFFA\BuildFFA */
    private static $plugin;
    /** @var int  */
    public $npcId = -1;
    /** @var bool  */
    public static $teaming = false;

    public function onEnable()
    {
        self::$plugin = $this;

        foreach (scandir($this->getServer()->getDataPath()."worlds") as $world) {
            if($world != "." && $world != "..") {
                Server::getInstance()->loadLevel($world);
                Server::getInstance()->getLevelByName($world)->setTime(6000);
                Server::getInstance()->getLevelByName($world)->stopTime();
            }
        }

        if(!InvMenuHandler::isRegistered())
            InvMenuHandler::register($this);
        $this->registerEvents();
        $this->getScheduler()->scheduleRepeatingTask(new DelayTask(), 5);
        $this->getScheduler()->scheduleRepeatingTask(new GameTask(), 20);
        $this->getScheduler()->scheduleRepeatingTask(new AnimationTask(), 1);
        $this->getServer()->getCommandMap()->register('inv', new InvSortCommand());
        $this->getServer()->getCommandMap()->register('forcevote', new ForceVotingCommand());
        $this->getServer()->getCommandMap()->register('skip', new SkipCommand());

        VIPJoinProvider::enable(30);
        AsyncExecutor::submitMySQLAsyncTask("BuildFFA", function (\mysqli $mysqli) {
            $mysqli->query("CREATE TABLE IF NOT EXISTS inventories(id INTEGER NOT NULL KEY AUTO_INCREMENT, playername varchar(32) NOT NULL, sort TEXT NOT NULL)");
        });
    }

    protected function registerEvents() {
        $events = [
            new BlockBreakListener(),
            new BlockPlaceListener(),
            new DamageListener(),
            new FeedListener(),
            new InvTransactionListener(),
            new PlayerJoinListener(),
            new InteractListener(),
            new ProjectileShootListener(),
            new DropItemListener(),
            new ProjectileHitBlockListener(),
            new BlockUpdateListener()
        ];

        foreach ($events as $event) {
            $this->getServer()->getPluginManager()->registerEvents($event, $this);
        }
    }

    /**
     * @return BuildFFA
     */
    public static function getPlugin(): BuildFFA
    {
        return self::$plugin;
    }
}