<?php


namespace BauboLP\LobbySystem\Provider;


use BauboLP\Cloud\CloudBridge;
use BauboLP\Cloud\Packets\PlayerMessagePacket;
use BauboLP\Core\Player\RyzerPlayerProvider;
use BauboLP\Core\Provider\AsyncExecutor;
use BauboLP\Core\Provider\CoinProvider;
use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\Core\Provider\MySQLProvider;
use BauboLP\Core\Ryzer;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Utils\LobbyPlayer;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class LottoProvider
{
    /**
     * @param \pocketmine\Player $player
     */
    public function sendLottoMenu(Player $player)
    {
        $inv = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST)
            ->setName(LobbySystem::Prefix . TextFormat::AQUA . "Lotto")
            ->setListener(function (InvMenuTransaction $transaction) use ($player): InvMenuTransactionResult{
                $item = $transaction->getItemClicked();
                $action = $transaction->getAction();
                if($item->getId() != Item::ENDER_CHEST) return $transaction->discard();
                $slot = $action->getSlot();
                $win = LottoProvider::getRandomWin();
                $winItem = LottoProvider::getItemByInt($win);
                $inv = $action->getInventory();

                $inv->setItem($slot, $winItem);
                if(($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                    $obj->addLottoWin($win);
                    if(count($obj->getLottoWin()) > 4) {
                        for($i = 0; $i < 54; $i++) {
                            $inv->setItem($i, Item::get(Item::GLASS_PANE));
                        }
                        $coins = 0;
                        foreach ($obj->getLottoWin() as $win)
                            $coins += $win;

                        $winItem = Item::get(Item::DIAMOND_BLOCK, 0, 1)->setCustomName(TextFormat::GREEN.TextFormat::BOLD.$coins." Coins");
                        $inv->setItem(20, LottoProvider::getItemByInt($obj->getLottoWin()[0]));
                        $inv->setItem(21, LottoProvider::getItemByInt($obj->getLottoWin()[1]));
                        $inv->setItem(22, LottoProvider::getItemByInt($obj->getLottoWin()[2]));
                        $inv->setItem(23, LottoProvider::getItemByInt($obj->getLottoWin()[3]));
                        $inv->setItem(24, LottoProvider::getItemByInt($obj->getLottoWin()[4]));
                        CoinProvider::addCoins($player->getName(), $coins);
                        $obj->setLottoWin([]);

                        if($coins > 5000 && $coins < 25000) {
                            foreach (Server::getInstance()->getOnlinePlayers() as $players)
                                $players->sendMessage("\n\n".LobbySystem::Prefix.LanguageProvider::getMessageContainer('lobby-lotto-win', $players->getName(), ['#coins' => $coins, '#playername' => $player->getName()]));
                        }else if($coins >= 25000) {
                            $pk = new PlayerMessagePacket();
                            $pk->players = "ALL";
                            $pk->message = "\n\n&aLOTTERIE &8-> &a".$player->getName()." &fhat einen Gewinn von &e".$coins." Coins &fgemacht! MASHALLA :)";
                            CloudBridge::getInstance()->getClient()->getPacketHandler()->writePacket($pk);
                        }
                    }
                }
                return $transaction->discard();
            });
        for ($i = 0; $i < 54; $i++)
            $inv->getInventory()->setItem($i, Item::get(Item::ENDER_CHEST)->setCustomName(TextFormat::GOLD . "???"));
        $inv->send($player);
    }


    /**
     * @param \BauboLP\LobbySystem\Utils\LobbyPlayer $player
     * @param int $count
     */
    public function addTicket(LobbyPlayer $player, int $count = 1)
    {
        $player->setTickets($player->getTickets() + $count);
        Server::getInstance()->getAsyncPool()->submitTask(new class($player->getPlayer()->getName(), MySQLProvider::getMySQLData(), $count) extends AsyncTask{
            /** @var string  */
            private $playerName;
            /** @var array  */
            private $mysqlData;
            /** @var int */
            private $count;

            public function __construct(string $playerName, array $mysqlData, int $count)
            {
                $this->playerName = $playerName;
                $this->mysqlData = $mysqlData;
                $this->count = $count;
            }

            /**
             * @inheritDoc
             */
            public function onRun()
            {
                $playerName = $this->playerName;
                $count = $this->count;

                $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');
                $mysqli->query("UPDATE LottoTickets SET tickets=tickets+'$count' WHERE playername='$playerName'");
                $mysqli->close();
            }
        });
    }

    /**
     * @param \BauboLP\LobbySystem\Utils\LobbyPlayer $player
     * @param int $count
     */
    public function removeTicket(LobbyPlayer $player, int $count = 1)
    {
        $player->setTickets($player->getTickets() - $count);
        Server::getInstance()->getAsyncPool()->submitTask(new class($player->getPlayer()->getName(), MySQLProvider::getMySQLData(), $count) extends AsyncTask{
            /** @var string  */
            private $playerName;
            /** @var array  */
            private $mysqlData;
            /** @var int */
            private $count;

            public function __construct(string $playerName, array $mysqlData, int $count)
            {
                $this->playerName = $playerName;
                $this->mysqlData = $mysqlData;
                $this->count = $count;
            }

            /**
             * @inheritDoc
             */
            public function onRun()
            {
                $playerName = $this->playerName;
                $count = $this->count;

                $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');
                $mysqli->query("UPDATE LottoTickets SET tickets=tickets-'$count' WHERE playername='$playerName'");
                $mysqli->close();
            }
        });
    }

    /**
     * @return int
     */
    public static function getRandomWin(): int
    {
        $r = rand(50, 500000);
        $r = rand($r, 5000);
        $r = rand($r, 50000);
        $r = rand($r, 5000);
        $r = rand($r, 500);
        $r = rand($r, 50);
        $r = rand($r, 14);
        $r = rand($r, 23);
        $r = rand($r, 4);
        $r = rand($r, 14);
        $r = rand($r, 1);
        $r = rand($r, 60);
        $r = rand($r, 2);
        return $r;
    }

    /**
     * @param int $int
     * @return \pocketmine\item\Item
     */
    public static function getItemByInt(int $int): Item
    {
        if($int < 300) {
            return Item::get(Item::COAL)->setCustomName(TextFormat::WHITE.$int." Coins");
        }else if($int < 1000 && $int > 300) {
            return Item::get(Item::IRON_INGOT)->setCustomName(TextFormat::GRAY.$int." Coins");
        }else if($int < 5000 && $int > 1000) {
            return Item::get(Item::REDSTONE)->setCustomName(TextFormat::YELLOW.TextFormat::BOLD.$int." Coins");
        }else if($int < 10000 && $int > 5000) {
            return Item::get(Item::GOLD_INGOT)->setCustomName(TextFormat::GOLD.TextFormat::BOLD.$int." Coins");
        }else if($int > 10000 && $int < 30000) {
            return Item::get(Item::DIAMOND)->setCustomName(TextFormat::LIGHT_PURPLE.TextFormat::BOLD.$int." Coins");
        }else if($int > 30000) {
            return Item::get(Item::DIAMOND_BLOCK)->setCustomName(TextFormat::AQUA.TextFormat::BOLD.$int." Coins");
        }

        return Item::get(Item::ANVIL)->setCustomName(TextFormat::RED."ERROR!");
    }
}