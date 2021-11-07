<?php


namespace BauboLP\LobbySystem\Tasks;


use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\Core\Utils\HoloGram;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\ItemProvider;
use BauboLP\LobbySystem\Provider\LobbyGamesProvider;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class LoadDataAsyncTask extends AsyncTask
{
    /** @var string */
    private $playerName;
    /** @var array */
    private $mysqlData;
    /** @var int  */
    private $time;

    public function __construct(string $playerName, array $mysqlData)
    {
        $this->playerName = $playerName;
        $this->mysqlData = $mysqlData;
        $this->time = time();
    }

    public function onRun()
    {
        $playerName = $this->playerName;
        $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');

        $result = $mysqli->query("SELECT * FROM LobbyPlayer WHERE playername='$playerName'");
        $pData = [];

        if($result->num_rows > 0) {
            while($data = $result->fetch_assoc()) {
                $pData['particles'] = explode(":", $data['particles']);
                $pData['particle'] = $data['particle'];
                $pData['fallitems'] = explode(":", $data['fallitems']);
                $pData['fallitem'] = $data['fallitem'];
                $pData['wings'] = explode(":", $data['wings']);
                $pData['wing'] = $data['wing'];
                $pData['specials'] = explode(":", $data['specials']);
                $pData['special'] = $data['special'];
                $pData['walkingBlocks'] = explode(":", $data['walkingblocks']);
                $pData['walkingBlock'] = $data['walkingblock'];
            }
        }else {
            $mysqli->query("INSERT INTO `LobbyPlayer`(`playername`, `particles`, `particle`, `fallitems`, `fallitem`, `wings`, `wing`, `specials`, `special`, `walkingblocks`, `walkingblock`) VALUES ('$playerName', '', '', '', '', '', '', '', '', '', '')");
            $pData['particles'] = [];
            $pData['particle'] = "";
            $pData['fallitems'] = [];
            $pData['fallitem'] = "";
            $pData['wings'] = [];
            $pData['wing'] = "";
            $pData['specials'] = [];
            $pData['special'] = "";
            $pData['walkingBlocks'] = [];
            $pData['walkingBlock'] = "";
        }

        $result = $mysqli->query("SELECT * FROM DailyReward WHERE playername='$playerName'");
        if($result->num_rows > 0) {
            while ($data = $result->fetch_assoc()) {
                $pData['dailyCoins'] = $data['coins'];
                $pData['dailyLotto'] = $data['lottoticket'];
                $pData['dailyCoinBomb'] = $data['coinbomb'];
            }
        }else {
            $now = time();
            $mysqli->query("INSERT INTO `DailyReward`(`playername`, `coins`, `lottoticket`, `coinbomb`) VALUES ('$playerName', '$now', '$now', '$now')");
            $pData['dailyCoins'] = $now;
            $pData['dailyLotto'] = $now;
            $pData['dailyCoinBomb'] = $now;
        }

        $result = $mysqli->query("SELECT * FROM LoginStreak WHERE playername='$playerName'");
        if($result->num_rows > 0) {
            while($data = $result->fetch_assoc()) {
                $pData['loginstreak'] = $data['loginstreak'];
                $pData['nextday'] = $data['nextstreakday'];
                $pData['lastday'] = $data['laststreakday'];
            }
        }else {
            $now = strtotime("next day");
            $now2 = $this->time;
            $mysqli->query("INSERT INTO `LoginStreak`(`playername`, `loginstreak`, `nextstreakday`, `laststreakday`) VALUES ('$playerName', '1', '$now', '$now2')");
            $pData['loginstreak'] = 1;
            $pData['nextday'] = $now;
            $pData['lastday'] = $now2;
        }

        $result = $mysqli->query("SELECT * FROM CoinBomb WHERE playername='$playerName'");
        if($result->num_rows > 0) {
            while($data = $result->fetch_assoc()) {
                $pData['bombs'] = $data['bombs'];
            }
        }else {
            $mysqli->query("INSERT INTO `CoinBomb`(`playername`, `bombs`) VALUES ('$playerName', '0')");
            $pData['bombs'] = 0;
        }

        $result = $mysqli->query("SELECT * FROM LottoTickets WHERE playername='$playerName'");
        if($result->num_rows > 0) {
            while($data = $result->fetch_assoc()) {
                $pData['tickets'] = $data['tickets'];
            }
        }else {
            $mysqli->query("INSERT INTO `LottoTickets`(`playername`, `tickets`) VALUES ('$playerName', '0')");
            $pData['tickets'] = 0;
        }

        $result = $mysqli->query("SELECT * FROM `Status` WHERE playername='$playerName'");
        if($result->num_rows <= 0) {
            $mysqli->query("INSERT INTO `Status`(`playername`, `status`) VALUES ('$playerName', 'false')");
        }

        $result = $mysqli->query("SELECT * FROM `JumpAndRun` WHERE playername='$playerName'");
        if($result->num_rows <= 0) {
            $pData['time'] = "0:00";
        }else {
            while($data = $result->fetch_assoc()) {
                $pData['time'] = $data['time'];
            }
        }
        //var_dump($mysqli->error_list);
        $mysqli->close();
        $this->setResult($pData);
    }

    public function onCompletion(Server $server)
    {
        $data = $this->getResult();
        if(($obj = LobbySystem::getPlayerCache($this->playerName)) != null) {
            $obj->setParticle($data['particle']);
            $obj->setParticles($data['particles']);
            $obj->setWings($data['wings']);
            $obj->setWing($data['wing']);
            $obj->setSpecial($data['special']);
            $obj->setSpecials($data['specials']);
            $obj->setFallItems($data['fallitems']);
            $obj->setFallItem($data['fallitem']);
            $obj->setDailyCoins((int)$data['dailyCoins']);
            $obj->setDailyLotto((int)$data['dailyLotto']);
            $obj->setDailyCoinBomb((int)$data['dailyCoinBomb']);
            $obj->setLoginStreak((int)$data['loginstreak']);
            $obj->setLoginStreak((int)$data['loginstreak']);
            $obj->setNextStreakDay((int)$data['nextday']);
            $obj->setLastStreakDay((int)$data['lastday']);
            $obj->setCoinBombs((int)$data['bombs']);
            $obj->setTickets((int)$data['tickets']);
            $obj->setBestJumpAndRunTime($data['time']);
            $obj->setWalkingBlock($data["walkingBlock"]);
            $obj->setWalkingBlocks($data["walkingBlocks"]);
         //   $obj->getPlayer()->sendMessage("\n".LobbySystem::Prefix.LanguageProvider::getMessageContainer('lobby-data-loaded', $this->playerName));
            $obj->getPlayer()->playSound('note.pling', 5, 1.0, [$obj->getPlayer()]);
            $obj->checkLoginStreak();
            $obj->loadWingObject();

            $holo = new HoloGram(LobbyGamesProvider::$jumpAndRunStartVec, TextFormat::DARK_GRAY."-= ".TextFormat::AQUA."J&R ".TextFormat::DARK_GRAY."=-\n".TextFormat::GRAY."Your best time: ".TextFormat::YELLOW.$obj->getBestJumpAndRunTime());
            $obj->setJarHolo($holo);
            if(($player = Server::getInstance()->getPlayerExact($this->playerName))) {
                ItemProvider::giveLobbyItems($player);
                $server->getDefaultLevel()->addParticle($holo, [$player]);
            }
        }
    }
}