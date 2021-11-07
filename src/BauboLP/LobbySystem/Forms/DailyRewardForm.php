<?php


namespace BauboLP\LobbySystem\Forms;


use BauboLP\Core\Player\RyzerPlayerProvider;
use BauboLP\Core\Provider\CoinProvider;
use BauboLP\Core\Provider\MySQLProvider;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Utils\LobbyPlayer;
use pocketmine\form\BaseForm;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\network\mcpe\protocol\EmotePacket;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class DailyRewardForm extends MenuForm
{

    const CAN_GET = 0;
    const MUST_WAIT = 1;

    public function __construct(LobbyPlayer $player)
    {
        $options = [];
        $now = time();
        $states = [];

        if($player->getDailyCoins() > $now) {
            $states[] = self::MUST_WAIT;
            $options[] = new MenuOption(TextFormat::RED."200 Coins"."\n".TextFormat::YELLOW."Already received today");
        }else {
            $states[] = self::CAN_GET;
            $options[] = new MenuOption(TextFormat::GREEN."200 Coins"."\n".TextFormat::GOLD."Click to receive");
        }

        if($player->getDailyLotto() > $now) {
            $states[] = self::MUST_WAIT;
            $options[] = new MenuOption(TextFormat::RED."Lotto Ticket"."\n".TextFormat::YELLOW."Already received today");
        }else {
            $states[] = self::CAN_GET;
            $options[] = new MenuOption(TextFormat::GREEN."Lotto Ticket"."\n".TextFormat::GOLD."Click to receive");
        }

        if($player->getDailyCoinBomb() > $now) {
            $states[] = self::MUST_WAIT;
            $options[] = new MenuOption(TextFormat::RED."Coin Bomb"."\n".TextFormat::YELLOW."Already received today");
        }else {
            if($player->getPlayer()->hasPermission("lobby.coinbomb")) {
                $states[] = self::CAN_GET;
                $options[] = new MenuOption(TextFormat::GREEN."Coin Bomb"."\n".TextFormat::GOLD."Click to receive");
            }else {
                $states[] = self::MUST_WAIT;
                $options[] = new MenuOption(TextFormat::GREEN."Coin Bomb"."\n".TextFormat::DARK_RED."LOCKED");
            }
        }

        $options[] = new MenuOption(TextFormat::YELLOW."Loginstreak ".TextFormat::DARK_GRAY."> ".TextFormat::GOLD.$player->getLoginStreak()."\n".TextFormat::RED."Lose streak: ".date("Y-m-d", $player->getNextStreakDay()));


        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA."Daily Reward", "", $options, function (Player $player, int $selectedOption) use ($states): void {
                switch ($selectedOption) {
                    case 0:
                        if($states[$selectedOption] == DailyRewardForm::CAN_GET) {
                            $time = strtotime("next day");
                            if(($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                                $obj->setDailyCoins($time);
                                $player->sendForm(new DailyRewardForm($obj));
                            }
                            $player->playSound('random.levelup', 5, 1.0, [$player]);
                            Server::getInstance()->getAsyncPool()->submitTask(new class($player->getName(), MySQLProvider::getMySQLData(), $time) extends AsyncTask{
                                /** @var string  */
                                private $playerName;
                                /** @var array  */
                                private $mysqlData;
                                /** @var int  */
                                private $time;

                                public function __construct(string $playerName, array $mysqlData, int $time)
                                {
                                    $this->playerName = $playerName;
                                    $this->mysqlData = $mysqlData;
                                    $this->time = $time;
                                }

                                /**
                                 * @inheritDoc
                                 */
                                public function onRun()
                                {
                                    $playerName = $this->playerName;
                                    $time = $this->time;

                                    $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');
                                    $mysqli->query("UPDATE DailyReward SET coins='$time' WHERE playername='$playerName'");
                                    $mysqli->close();
                                }

                                public function onCompletion(Server $server)
                                {
                                    CoinProvider::addCoins($this->playerName, 200);
                                }
                            });
                        }
                        break;
                    case 1:
                        if($states[$selectedOption] == DailyRewardForm::CAN_GET) {
                            $time = strtotime("next day");
                            $player->playSound('random.levelup', 5, 1.0, [$player]);
                            if(($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                                $obj->setDailyLotto($time);
                                $obj->setTickets($obj->getTickets() + 1);
                                $player->sendForm(new DailyRewardForm($obj));
                            }
                            Server::getInstance()->getAsyncPool()->submitTask(new class($player->getName(), MySQLProvider::getMySQLData(), $time) extends AsyncTask{
                                /** @var string  */
                                private $playerName;
                                /** @var array  */
                                private $mysqlData;
                                /** @var int  */
                                private $time;

                                public function __construct(string $playerName, array $mysqlData, int $time)
                                {
                                    $this->playerName = $playerName;
                                    $this->mysqlData = $mysqlData;
                                    $this->time = $time;
                                }

                                /**
                                 * @inheritDoc
                                 */
                                public function onRun()
                                {
                                    $playerName = $this->playerName;
                                    $time = $this->time;

                                    $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');
                                    $mysqli->query("UPDATE DailyReward SET lottoticket='$time' WHERE playername='$playerName'");
                                    $mysqli->query("UPDATE LottoTickets SET tickets=tickets+1 WHERE playername='$playerName'");
                                    $mysqli->close();
                                }
                            });
                        }
                        break;
                    case 2:
                        if($states[$selectedOption] == DailyRewardForm::CAN_GET) {
                            $time = strtotime("next day");
                            $player->playSound('random.levelup', 5, 1.0, [$player]);
                            if(($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                                $obj->setDailyCoinBomb($time);
                                $obj->setCoinBombs($obj->getCoinBombs() + 1);
                                $player->sendForm(new DailyRewardForm($obj));
                            }
                            Server::getInstance()->getAsyncPool()->submitTask(new class($player->getName(), MySQLProvider::getMySQLData(), $time) extends AsyncTask{
                                /** @var string  */
                                private $playerName;
                                /** @var array  */
                                private $mysqlData;
                                /** @var int  */
                                private $time;

                                public function __construct(string $playerName, array $mysqlData, int $time)
                                {
                                    $this->playerName = $playerName;
                                    $this->mysqlData = $mysqlData;
                                    $this->time = $time;
                                }

                                /**
                                 * @inheritDoc
                                 */
                                public function onRun()
                                {
                                    $playerName = $this->playerName;
                                    $time = $this->time;

                                    $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');
                                    $mysqli->query("UPDATE DailyReward SET coinbomb='$time' WHERE playername='$playerName'");
                                    $mysqli->query("UPDATE CoinBomb SET bombs=bombs+1 WHERE playername='$playerName'");
                                    $mysqli->close();
                                }
                            });
                        }
                        break;
                }
        });
    }

}