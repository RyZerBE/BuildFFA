<?php


namespace BauboLP\LobbySystem\Provider;


use BauboLP\Core\Provider\MySQLProvider;
use BauboLP\Core\Ryzer;
use BauboLP\LobbySystem\LobbySystem;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class CreatorCodeProvider
{
    /** @var array  */
    public static $creatorCodes = [];

    public static function loadCodes()
    {
        LobbySystem::getPlugin()->getScheduler()->scheduleRepeatingTask(new class extends Task{

            /**
             * @inheritDoc
             */
            public function onRun(int $currentTick)
            {
                Ryzer::getMysqlProvider()->exec(new class extends AsyncTask{

                    private $mysqlData;

                    public function __construct()
                    {
                        $this->mysqlData = MySQLProvider::getMySQLData();
                    }

                    /**
                     * @inheritDoc
                     */
                    public function onRun()
                    {
                        $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');

                        $result = $mysqli->query("SELECT * FROM CreatorCode");
                        $codes = [];
                        if($result->num_rows > 0) {
                            while($data = $result->fetch_assoc()) {
                                $codes[$data['code']] = ['percent' => $data['percent'], 'used' => $data['used'], 'canuse' => $data['canuse']];
                            }
                        }
                        $mysqli->close();
                        $this->setResult($codes);
                    }

                    public function onCompletion(Server $server)
                    {
                       CreatorCodeProvider::$creatorCodes = $this->getResult();
                    }
                });
            }
        }, 20 * 200);
    }

    public static function addCreatorCode(string $code, $percent, int $canuse = 1000000)
    {
        Ryzer::getMysqlProvider()->exec(new class($code, $percent, $canuse) extends AsyncTask{

            private $mysqlData;
            private $code;
            private $percent;
            private $canuse;

            public function __construct(string $code, $percent, $canuse)
            {
                $this->mysqlData = MySQLProvider::getMySQLData();
                $this->code = $code;
                $this->percent = $percent;
                $this->canuse = $canuse;
            }

            /**
             * @inheritDoc
             */
            public function onRun()
            {
                $canuse = $this->canuse;
                $code = $this->code;
                $percent = $this->percent;

                $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');
                $mysqli->query("INSERT INTO `CreatorCode`(`code`, `percent`, `used`, `canuse`) VALUES ('$code', '$percent', '0', '$canuse')");
                $mysqli->close();
            }
        });
    }

    /**
     * @param string $code
     */
    public static function removeCreatorCode(string $code)
    {
        Ryzer::getMysqlProvider()->exec(new class($code) extends AsyncTask{

            private $mysqlData;
            private $code;

            public function __construct(string $code)
            {
                $this->mysqlData = MySQLProvider::getMySQLData();
                $this->code = $code;
            }

            /**
             * @inheritDoc
             */
            public function onRun()
            {
                $code = $this->code;

                $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');
                $mysqli->query("DELETE FROM CreatorCode WHERE code='$code'");
                $mysqli->close();
            }
        });
    }

    /**
     * @param string $code
     * @return bool
     */
    public static function existCreatorCode(string $code): bool
    {
        return isset(self::$creatorCodes[$code]);
    }

    /**
     * @param string $code
     * @return array|null
     */
    public static function getCreatorCodeData(string $code): ?array
    {
        if(empty(self::$creatorCodes[$code])) return null;

        return self::$creatorCodes[$code];
    }
}