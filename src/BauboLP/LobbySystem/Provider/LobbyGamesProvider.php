<?php


namespace BauboLP\LobbySystem\Provider;


use BauboLP\Core\Player\RyzerPlayer;
use BauboLP\Core\Provider\MySQLProvider;
use BauboLP\LobbySystem\Utils\LobbyPlayer;
use pocketmine\block\Block;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class LobbyGamesProvider
{
    /** @var string  */
    const KING_OF_LEADER_FINISH = "800:200:800";

    const JUMP_AND_RUN_START = "6.5:98:5.5";
    const JUMP_AND_RUN_END = "38.5:106:42.5";

    /** @var string  */
    const POS1 = "274:146:158";
    /** @var string  */
    const POS2 = "300:159:184";
    /** @var \pocketmine\math\Vector3  */
    private static $vec;
    /** @var \pocketmine\math\Vector3  */
    public static $jumpAndRunStartVec;
    /** @var \pocketmine\math\Vector3  */
    private static $jumpAndRunEndVec;
    /** @var array  */
    private static $gameArea = [];

    public static $goalPlayer = null;

    public function __construct()
    {
        $ex = explode(":", self::KING_OF_LEADER_FINISH);
        self::$vec = new Vector3((float)$ex[0], (float)$ex[1], (float)$ex[2]);
        $ex = explode(":", self::JUMP_AND_RUN_START);
        self::$jumpAndRunStartVec = new Vector3((float)$ex[0], (float)$ex[1], (float)$ex[2]);
        $ex = explode(":", self::JUMP_AND_RUN_END);
        self::$jumpAndRunEndVec = new Vector3((float)$ex[0], (float)$ex[1], (float)$ex[2]);
        self::loadArea();
    }

    private static function loadArea()
    {
        $i1 = explode(":", self::POS1);
        $pos1 = new Position($i1[0], $i1[1], $i1[2]);
        $i2 = explode(":", self::POS2);
        $pos2 = new Position($i2[0], $i2[1], $i2[2]);
        for ($x = min($pos1->x, $pos2->x); $x <= max($pos1->x, $pos2->x); $x++) {
            for ($y = min($pos1->y, $pos2->y); $y < max($pos1->y, $pos2->y); $y++) {
                for ($z = min($pos1->z, $pos2->z); $z <= max($pos1->z, $pos2->z); $z++) {
                    self::$gameArea[] = "$x:$y:$z";
                    // var_dump("$x:$y:$z");
                }
            }
        }
    }

    public static function wantToStartJumpAndRun(Vector3 $vector3): bool
    {
        return $vector3->distance(self::$jumpAndRunStartVec) < 2;
    }

    public static function finishedJumpAndRun(Player $player): bool
    {
        return $player->asVector3()->distance(self::$jumpAndRunEndVec) < 2 && $player->getPlayer()->getLevel()->getBlock($player->getPlayer()->getSide(0))->getId() == Block::SEA_LANTERN;
    }

    /**
     * @return array
     */
    public static function getGameArea(): array
    {
        return self::$gameArea;
    }

    /**
     * @param \pocketmine\math\Vector3 $vector3
     * @return bool
     */
    public static function nearGame(Vector3 $vector3)
    {
        $stringVec = "{$vector3->getFloorX()}:{$vector3->getFloorY()}:{$vector3->getFloorZ()}";
        return in_array($stringVec, self::$gameArea);
    }

    /**
     * @param \pocketmine\math\Vector3 $vector3
     * @return bool
     */
    public static function isGoal(Vector3 $vector3)
    {
        return Server::getInstance()->getDefaultLevel()->getBlock($vector3->getSide(0))->getId() == Block::GOLD_BLOCK;
    }

    /**
     * @return null
     */
    public static function getGoalPlayer()
    {
        return self::$goalPlayer;
    }

    /**
     * @param null $goalPlayer
     */
    public static function setGoalPlayer($goalPlayer): void
    {
        self::$goalPlayer = $goalPlayer;
    }

    public static function spawnTop5OfJumpAndRun(string $playerName)
    {

        Server::getInstance()->getAsyncPool()->submitTask(new class($playerName) extends AsyncTask{

            private $mysqlData;
            /** @var string */
            private $playerName;

            public function __construct(string $playerName)
            {
                $this->playerName = $playerName;
                $this->mysqlData = MySQLProvider::getMySQLData();
            }

            /**
             * @inheritDoc
             */
            public function onRun()
            {
                $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');

                $result = $mysqli->query("SELECT * FROM `JumpAndRun`");

                $players = [];
                $normalTime = [];
                if($result->num_rows > 0) {
                   while($data = $result->fetch_assoc()) {
                      $time = explode(":", $data['time']);
                      $minutes = $time[0] * 60;
                      $resultOfTime = $minutes + $time[1];
                      $players[$data['playername']] = (int)$resultOfTime;
                      $normalTime[$data['playername']] = $data['time'];
                   }
                }

                $top5 = [];

                for($i = 0; $i < 5; $i++){
                    if(count($players) == 0) {
                        $top5[str_repeat("?", $i)] = TextFormat::RED."???";
                    }else {
                        $top = array_search(min($players), $players);
                        $top5[$top] = $normalTime[$top];
                        unset($players[$top]);
                    }
                }
                $mysqli->close();
                $this->setResult($top5);
            }

            public function onCompletion(Server $server)
            {
                $result = $this->getResult();
               // var_dump($result);
                $holo = TextFormat::DARK_GRAY."-= ".TextFormat::GOLD."TOP 5 ".TextFormat::DARK_GRAY."=-\n";

                foreach (array_keys($result) as $playerName) {
                    $holo .= TextFormat::AQUA."$playerName ".TextFormat::DARK_GRAY."-> ".TextFormat::GREEN.$result[$playerName]."\n";
                }

                if(($player = $server->getPlayerExact($this->playerName)))
                    $server->getDefaultLevel()->addParticle(new FloatingTextParticle(new Vector3(7.5, 99, 1.5), $holo), [$player]);
            }
        });
    }
}