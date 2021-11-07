<?php


namespace BauboLP\LobbySystem;


use BauboLP\BW\API\NPCAPI;
use BauboLP\Core\Provider\MySQLProvider;
use BauboLP\Core\Provider\VIPJoinProvider;
use BauboLP\Core\Ryzer;
use BauboLP\CWBWTraining\Loader;
use BauboLP\LobbySystem\Commands\BuildCommand;
use BauboLP\LobbySystem\Commands\CoinBombCommand;
use BauboLP\LobbySystem\Commands\FlyCommand;
use BauboLP\LobbySystem\Commands\PrivateServerCommand;
use BauboLP\LobbySystem\Commands\RewardCommand;
use BauboLP\LobbySystem\Commands\ShopCommand;
use BauboLP\LobbySystem\Commands\SpawnCommand;
use BauboLP\LobbySystem\Commands\StatusCommand;
use BauboLP\LobbySystem\Events\BlockPlaceBreakListener;
use BauboLP\LobbySystem\Events\DamageListener;
use BauboLP\LobbySystem\Events\DespawnListener;
use BauboLP\LobbySystem\Events\DropItemListener;
use BauboLP\LobbySystem\Events\ExhaustListener;
use BauboLP\LobbySystem\Events\ExplodeListener;
use BauboLP\LobbySystem\Events\InteractListener;
use BauboLP\LobbySystem\Events\InventoryPickUpListener;
use BauboLP\LobbySystem\Events\PlayerJoinListener;
use BauboLP\LobbySystem\Events\ReceivePacketListener;
use BauboLP\LobbySystem\Provider\AnimationProvider;
use BauboLP\LobbySystem\Provider\ConfigProvider;
use BauboLP\LobbySystem\Provider\CreatorCodeProvider;
use BauboLP\LobbySystem\Provider\LobbyGamesProvider;
use BauboLP\LobbySystem\Provider\LottoProvider;
use BauboLP\LobbySystem\Provider\NPCProvider;
use BauboLP\LobbySystem\Tasks\AddonTask;
use BauboLP\LobbySystem\Tasks\AFKTask;
use BauboLP\LobbySystem\Tasks\AnimationTask;
use BauboLP\LobbySystem\Tasks\BossBarTask;
use BauboLP\LobbySystem\Tasks\CooldownTask;
use BauboLP\LobbySystem\Tasks\DJTask;
use BauboLP\LobbySystem\Tasks\LobbyCountTask;
use BauboLP\LobbySystem\Tasks\LobbyGameTask;
use BauboLP\LobbySystem\Tasks\ScoreboardTask;
use BauboLP\LobbySystem\Tasks\ShieldTask;
use BauboLP\LobbySystem\Tasks\WalkingBlockTask;
use BauboLP\LobbySystem\Tasks\WingTask;
use BauboLP\LobbySystem\Utils\HatEntity;
use BauboLP\LobbySystem\Utils\LobbyPlayer;
use BauboLP\NPC\NPC;
use BlockHorizons\Fireworks\entity\FireworksRocket;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use xenialdan\apibossbar\BossBar;

class LobbySystem extends PluginBase
{
    const Prefix = TextFormat::YELLOW . TextFormat::BOLD . "Lobby " . TextFormat::RESET . TextFormat::DARK_GRAY . "| " . TextFormat::WHITE;

    /** @var \BauboLP\LobbySystem\LobbySystem */
    private static $plugin;
    /** @var \BauboLP\LobbySystem\Provider\ConfigProvider */
    private static $configProvider;
    /** @var \BauboLP\LobbySystem\Utils\LobbyPlayer[] */
    public static $players = [];
    /** @var LottoProvider */
    private static $lottoProvider;
    /** @var array */
    public static $runningClanWars = [];
    /** @var array  */
    public static $lobbys = [];
    /** @var array */
    public static $wingsData = [];
    /** @var BossBar */
    public static $bossBar;
    /** @var array  */
    public static $bossBarLines = [];

    const YEAR = 2021;
    const VIEW_DISTANCE = 12;

    /** @var array */
    public static $ranks = [
        "VIP" => ['cost' => 100000, 'name' => TextFormat::GOLD . "VIP", 'description' => [
            "Du erhältst den VIP Nametag",
            "Du erhältst das VIP Chatprefix",
            'Dein GG am Ende einer Runde wird hervorgehoben',
            'Du kannst in VOLLE Runden joinen',
            "Du kannst in der Lobby fliegen",
            "Du hast Zugriff auf alle Clanfarben, um dein Clantag farbenfroh zu gestalten",
            "Die Wertung Deiner Stimme ist bei der Mapauswahl x2",
            "Du erhältst doppelte Coins beim Abbauen von einem Bett in MLGRush/BedWars",
            "Spielsüchtig? Du erhältst 5 Lottotickets gratis",
            "Du kannst 80 Freunde haben",
            "Du erhältst den Rang auch auf unserem Discord Server (/verify)",
            "Du hast Zugriff auf den VIP-Talk auf unserem Discord"]],
        "Prime" => ['cost' => 250000, 'name' => TextFormat::AQUA . "Prime", 'description' => [
            "Du erhältst den Prime Nametag",
            "Du erhältst das Prime Chatprefix",
            'Dein GG am Ende einer Runde wird hervorgehoben',
            'Du kannst in VOLLE Runden joinen',
            "Du kannst in der Lobby fliegen",
            "Du hast Zugriff auf alle Clanfarben, um dein Clantag farbenfroh zu gestalten",
            "Die Wertung Deiner Stimme ist bei der Mapauswahl x3",
            "Du erhältst dreifache Coins beim Abbauen von einem Bett in MLGRush/BedWars",
            "Spielsüchtig? Du erhältst 5 Lottotickets gratis",
            "Du kannst 150 Freunde haben",
            "Du kannst PRIVATE SERVER erstellen",
            "Du kannst auf Deinem Privaten Server /start, /forcemap & /troll verwenden",
            "Du kannst Deinen Rang vor anderen Spielern verstecken",
            "Du kannst Dir einen Status von 24 Zeichen in der Lobby setzen",
            "Du erhältst Zugang zu möglichen Betas",
            "Die gewonnenen Coins aller Teilnehmer werden um 15% in BedWars geboostet",
            "Du erhältst den Rang auch auf unserem Discord Server (/verify)",
            "Du hast Zugriff auf den Prime-Talk auf unserem Discord"]]
    ];

    public function onEnable()
    {
        self::$plugin = $this;
        self::$configProvider = new ConfigProvider();
        new LobbyGamesProvider(); //load constructor..
        self::$lottoProvider = new LottoProvider();
        self::$bossBar = new BossBar();

        $this->registerEvents();
        $this->registerCommands();
         // MySQLProvider::getSQLConnection("Lobby")->getSql()->query("CREATE TABLE IF NOT EXISTS LobbyPlayer(id INTEGER NOT NULL KEY AUTO_INCREMENT, playername varchar(32) NOT NULL, particles TEXT NOT NULL, particle varchar(64) NOT NULL, fallitems TEXT NOT NULL, fallitem varchar(64) NOT NULL, wings TEXT NOT NULL, wing varchar(64) NOT NULL, specials TEXT NOT NULL, special varchar(64) NOT NULL, walkingblocks TEXT NOT NULL, walkingblock varchar(64) NOT NULL)");
        //   MySQLProvider::getSQLConnection("Lobby")->getSql()->query("CREATE TABLE IF NOT EXISTS DailyReward(id INTEGER NOT NULL KEY AUTO_INCREMENT, playername varchar(32) NOT NULL, coins integer NOT NULL, lottoticket integer NOT NULL, coinbomb integer NOT NULL)");
        //  MySQLProvider::getSQLConnection("Lobby")->getSql()->query("CREATE TABLE IF NOT EXISTS LoginStreak(id INTEGER NOT NULL KEY AUTO_INCREMENT, playername varchar(32) NOT NULL, loginstreak integer NOT NULL, nextstreakday integer NOT NULL, laststreakday integer NOT NULL)");
        //  MySQLProvider::getSQLConnection("Lobby")->getSql()->query("CREATE TABLE IF NOT EXISTS CoinBomb(id INTEGER NOT NULL KEY AUTO_INCREMENT, playername varchar(32) NOT NULL, bombs integer NOT NULL)");
         // MySQLProvider::getSQLConnection("Lobby")->getSql()->query("CREATE TABLE IF NOT EXISTS Status(id INTEGER NOT NULL KEY AUTO_INCREMENT, playername varchar(32) NOT NULL, status integer NOT NULL)");
         // MySQLProvider::getSQLConnection("Lobby")->getSql()->query("CREATE TABLE IF NOT EXISTS JumpAndRun(id INTEGER NOT NULL KEY AUTO_INCREMENT, playername varchar(32) NOT NULL, time varchar(20) NOT NULL)");

        CreatorCodeProvider::loadCodes();
        //Loader::registerScenarios();
       // Entity::registerEntity(HatEntity::class, true, ["HatEntity"]);
        $this->getScheduler()->scheduleRepeatingTask(new AnimationTask(), 3);
        $this->getScheduler()->scheduleRepeatingTask(new WalkingBlockTask(), 1);
        $this->getScheduler()->scheduleRepeatingTask(new AddonTask(), 5);
        $this->getScheduler()->scheduleRepeatingTask(new WingTask(), 20);
        $this->getScheduler()->scheduleRepeatingTask(new DJTask(), 5);
        $this->getScheduler()->scheduleRepeatingTask(new CooldownTask(), 5);
        $this->getScheduler()->scheduleRepeatingTask(new LobbyCountTask(), 200);
        $this->getScheduler()->scheduleRepeatingTask(new LobbyGameTask(), 5);
        $this->getScheduler()->scheduleRepeatingTask(new ShieldTask(), 5);
        $this->getScheduler()->scheduleRepeatingTask(new AFKTask(), 20);
        $this->getScheduler()->scheduleRepeatingTask(new ScoreboardTask(), 200);
        $this->getScheduler()->scheduleRepeatingTask(new BossBarTask(), 60);
        $this->getScheduler()->scheduleRepeatingTask(new class extends Task {

            /**
             * @inheritDoc
             */
            public function onRun(int $currentTick)
            {
                Ryzer::getMysqlProvider()->exec(new class extends AsyncTask {
                    /** @var array */
                    private $mysqlData;

                    public function __construct()
                    {
                        $this->mysqlData = MySQLProvider::getMySQLData();
                    }

                    public function onRun()
                    {
                        $mysqlData = $this->mysqlData;
                        $mysql = new \mysqli($mysqlData['host'] . ':3306', $mysqlData['user'], $mysqlData['password'], 'Clans');
                        $result = $mysql->query("SELECT * FROM RCW");
                        $rcw = [];
                        if ($result->num_rows > 0) {
                            while ($data = $result->fetch_assoc()) {
                                $rcw[$data['server']] = explode("*", $data['informations']);
                            }
                        }
                        $this->setResult($rcw);
                    }

                    public function onCompletion(Server $server)
                    {
                        if (!is_array($this->getResult())) return;

                        LobbySystem::$runningClanWars = $this->getResult();

                        foreach (Server::getInstance()->getDefaultLevel()->getEntities() as $entity) {
                            if($entity->namedtag->getString("Action", "#CoVid19") == "running_cw") {
                                $cws = array_keys(LobbySystem::$runningClanWars);
                              //  if(!is_array($cws)) return;

                                $running_count = count($cws);

                                if($running_count == 0) {
                                    Ryzer::renameEntity($entity->getId(), NPCProvider::$npc["running_cw"]["title"]."\n".TextFormat::DARK_GRAY."« ".TextFormat::RED."Kein laufendes Match".TextFormat::DARK_GRAY." »", "", Server::getInstance()->getOnlinePlayers());
                                  //  $entity->setNameTag(NPCProvider::$npc["running_cw"]["title"]."\n".TextFormat::DARK_GRAY."« ".TextFormat::RED."Kein laufendes Match".TextFormat::DARK_GRAY." »");
                                }else if($running_count == 1) {
                                    Ryzer::renameEntity($entity->getId(), NPCProvider::$npc["running_cw"]["title"]."\n".TextFormat::DARK_GRAY."« ".TextFormat::GRAY."Es wird ".TextFormat::AQUA."ein Match ".TextFormat::GRAY."bestritten".TextFormat::DARK_GRAY." »", "", Server::getInstance()->getOnlinePlayers());
                                }else {
                                    Ryzer::renameEntity($entity->getId(), NPCProvider::$npc["running_cw"]["title"]."\n".TextFormat::DARK_GRAY."« ".TextFormat::GRAY."Es werden ".TextFormat::AQUA.$running_count." Matches ".TextFormat::GRAY."bestritten".TextFormat::DARK_GRAY." »", "", Server::getInstance()->getOnlinePlayers());
                                }
                                break;
                            }
                        }
                    }
                });
            }
        }, 20 * 5);
        
        //$this->saveResource("/root/RyzerCloud/data/Lobby/Wings/example.yml");
        foreach(glob("/root/RyzerCloud/data/Lobby/Wings/*.yml") as $wingPath){
            $wingName = pathinfo($wingPath, PATHINFO_FILENAME);
            self::$wingsData[$wingName] = yaml_parse_file($wingPath);
        }

        if(!file_exists("/root/RyzerCloud/data/Lobby/bossbar.yml")) {
            $config = new Config("/root/RyzerCloud/data/Lobby/bossbar.yml", Config::YAML);
            $config->set("Lines", ["&b&lTEST 1", "&6&lTEST 2", "&f&lTEST 3"]);
            $config->save();
        }
        $config = new Config("/root/RyzerCloud/data/Lobby/bossbar.yml", Config::YAML);
        foreach ($config->get("Lines") as $line) {
            self::$bossBarLines[] = str_replace("&", TextFormat::ESCAPE, $line);
        }

        InvMenuHandler::register($this);
        Server::getInstance()->getDefaultLevel()->setTime(6000);
        Server::getInstance()->getDefaultLevel()->stopTime();

       /* VIPJoinProvider::activate();
        VIPJoinProvider::activateChecks(); //Tests
        VIPJoinProvider::setPlayers(1);*/
    }

    protected function registerEvents()
    {
        $events = [
            new DamageListener(),
            new DropItemListener(),
            new ExhaustListener(),
            new PlayerJoinListener(),
            new BlockPlaceBreakListener(),
            new InteractListener(),
            new InventoryPickUpListener(),
            new ExplodeListener(),
            new ReceivePacketListener(),
            new DespawnListener()
        ];

        foreach ($events as $event) {
            Server::getInstance()->getPluginManager()->registerEvents($event, $this);
        }
    }

    protected function registerCommands()
    {
        $commands = [
            'spawn' => new SpawnCommand(),
            'fly' => new FlyCommand(),
            'build' => new BuildCommand(),
            'pserver' => new PrivateServerCommand(),
            'reward' => new RewardCommand(),
            'coinbomb' => new CoinBombCommand(),
            'shop' => new ShopCommand(),
            'status' => new StatusCommand()
        ];

        $map = Server::getInstance()->getCommandMap();
        foreach (array_keys($commands) as $command) {
            $map->register($command, $commands[$command]);
        }
    }

    /**
     * @return array
     */
    public static function getWingsData(): array
    {
        return self::$wingsData;
    }

    /**
     * @return \BauboLP\LobbySystem\LobbySystem
     */
    public static function getPlugin(): LobbySystem
    {
        return self::$plugin;
    }

    /**
     * @return \BauboLP\LobbySystem\Provider\ConfigProvider
     */
    public static function getConfigProvider(): ConfigProvider
    {
        return self::$configProvider;
    }

    /**
     * @param string $playerName
     * @return \BauboLP\LobbySystem\Utils\LobbyPlayer|null
     */
    public static function getPlayerCache(string $playerName): ?LobbyPlayer
    {
        if (isset(self::$players[$playerName])) return self::$players[$playerName];

        return nulL;
    }

    /**
     * @param \pocketmine\math\Vector3 $vector3
     * @param int $type
     * @param string $color
     * @return void
     */
    public static function createFirework(Vector3 $vector3, int $type, string $color): void
    {
        /** @var \BlockHorizons\Fireworks\item\Fireworks $fw */
        $fw = ItemFactory::get(Item::FIREWORKS);

        $fw->addExplosion($type, $color, "", false, false);
        $fw->setFlightDuration(2);

        $level = Server::getInstance()->getDefaultLevel();

        $nbt = FireworksRocket::createBaseNBT($vector3, new Vector3(0.001, 0.05, 0.001), lcg_value() * 360, 90);
        $entity = FireworksRocket::createEntity("FireworksRocket", $level, $nbt, $fw);

        if ($entity instanceof FireworksRocket) {
            $entity->spawnToAll();
        }
    }

    /**
     * @return LottoProvider
     */
    public static function getLottoProvider(): LottoProvider
    {
        return self::$lottoProvider;
    }
}