<?php


namespace BauboLP\LobbySystem\Commands;


use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\Core\Provider\MySQLProvider;
use BauboLP\Core\Ryzer;
use BauboLP\LobbySystem\LobbySystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class CoinBombCommand extends Command
{

    public function __construct()
    {
        parent::__construct('coinbomb', "", "", []);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player) return;

        if(($obj = LobbySystem::getPlayerCache($sender->getName())) != null) {
            if($obj->getCoinBombs() <= 0) {
                $sender->sendMessage(LobbySystem::Prefix.LanguageProvider::getMessageContainer('lobby-no-coin-bombs', $sender->getName()));
                return;
            }

            $obj->setCoinBombs($obj->getCoinBombs() - 1);
            Ryzer::getMysqlProvider()->exec(new class($sender->getName()) extends AsyncTask{
                /** @var string */
                private $playerName;
                /** @var array */
                private $mysqlData;

                public function __construct(string $playerName)
                {
                    $this->mysqlData = MySQLProvider::getMySQLData();
                    $this->playerName = $playerName;
                }

                /**
                 * @inheritDoc
                 */
                public function onRun()
                {
                    $playerName = $this->playerName;
                    $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');
                    $mysqli->query("UPDATE CoinBomb SET bombs=bombs-1 WHERE playername='$playerName'");
                    $mysqli->close();
                }

                public function onCompletion(Server $server)
                {
                    if(($player = $server->getPlayerExact($this->playerName)) != null) {
                        $player->getInventory()->addItem(Item::get(Item::GOLD_NUGGET, 0, 1)->setCustomName(TextFormat::AQUA."CoinBomb"));
                        $player->playSound('random.orb', 5, 1.0, [$player]);
                    }
                }
            });
        }
    }
}