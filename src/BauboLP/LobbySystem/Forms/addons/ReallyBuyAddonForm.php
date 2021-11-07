<?php


namespace BauboLP\LobbySystem\Forms\addons;


use BauboLP\BW\BW;
use BauboLP\Core\Player\RyzerPlayerProvider;
use BauboLP\Core\Provider\CoinProvider;
use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\Core\Provider\MySQLProvider;
use BauboLP\LobbySystem\LobbySystem;
use pocketmine\form\ModalForm;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class ReallyBuyAddonForm extends ModalForm
{

    const PARTICLE = 0;
    const FALL_ITEM = 1;
    const WING = 2;
    const SPECIAL = 3;
    const HATS = 4;
    const WALKING_BLOCKS = 5;

    public function __construct(string $playerName, string $addon, int $cost, int $type)
    {
        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA."Sure?", LanguageProvider::getMessageContainer('lobby-really-buy-addon', $playerName, ['#addon' => $addon, '#cost' => $cost]), function (Player $player, bool $choice) use($type, $addon, $cost): void{
            if($choice) {
                if(($obj = RyzerPlayerProvider::getRyzerPlayer($player->getName())) != null) {
                    if($obj->getCoins() < $cost) {
                        $player->sendMessage(LobbySystem::Prefix.LanguageProvider::getMessageContainer('not-enough-coins', $player->getName()));
                        return;
                    }
                }
                if($type == self::PARTICLE) {
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        $addons = implode(":", $obj->getParticles());
                        $addons .= ":$addon";
                        Server::getInstance()->getAsyncPool()->submitTask(new class($player->getName(), $addon, $cost, $addons, MySQLProvider::getMySQLData()) extends AsyncTask {

                            private $playerName;
                            private $addon;
                            private $addons;
                            private $cost;
                            private $mysqlData;

                            public function __construct(string $playerName, string $addon, int $cost, string $addons, $mysqlData)
                            {
                                $this->playerName = $playerName;
                                $this->addon = $addon;
                                $this->addons = $addons;
                                $this->cost = $cost;
                                $this->mysqlData = $mysqlData;
                            }

                            /**
                             * @inheritDoc
                             */
                            public function onRun()
                            {
                                $playerName = $this->playerName;
                                $addon = $this->addon;
                                $addons = $this->addons;
                                $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');

                                $mysqli->query("UPDATE `LobbyPlayer` SET particles='$addons',particle='$addon' WHERE playername='$playerName'");
                                $this->setResult($addon);
                                $mysqli->close();
                            }

                            public function onCompletion(Server $server)
                            {
                                CoinProvider::removeCoins($this->playerName, $this->cost);
                                if (($obj = LobbySystem::getPlayerCache($this->playerName)) != null) {
                                    $obj->setParticle($this->getResult());
                                    $particles = $obj->getParticles();
                                    $particles[] = $this->getResult();
                                    $obj->setParticles($particles);
                                    $obj->getPlayer()->playSound('random.levelup', 5, 1.0, [$obj->getPlayer()]);
                                    $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-successful-bought', $obj->getPlayer()->getName(), ['#addon' => $this->addon, '#cost' => $this->cost]));
                                }
                            }
                        });
                    }
                }else if($type == self::FALL_ITEM) {
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        $addons = implode(":", $obj->getFallItems());
                        $addons .= ":$addon";
                        Server::getInstance()->getAsyncPool()->submitTask(new class($player->getName(), $addon, $cost, $addons, MySQLProvider::getMySQLData()) extends AsyncTask {

                            private $playerName;
                            private $addon;
                            private $addons;
                            private $cost;
                            private $mysqlData;

                            public function __construct(string $playerName, string $addon, int $cost, string $addons, $mysqlData)
                            {
                                $this->playerName = $playerName;
                                $this->addon = $addon;
                                $this->addons = $addons;
                                $this->cost = $cost;
                                $this->mysqlData = $mysqlData;
                            }

                            /**
                             * @inheritDoc
                             */
                            public function onRun()
                            {
                                $playerName = $this->playerName;
                                $addon = $this->addon;
                                $addons = $this->addons;
                                $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');

                                $mysqli->query("UPDATE `LobbyPlayer` SET fallitems='$addons',fallitem='$addon' WHERE playername='$playerName'");
                                $this->setResult($addon);
                                $mysqli->close();
                            }

                            public function onCompletion(Server $server)
                            {
                                CoinProvider::removeCoins($this->playerName, $this->cost);
                                if (($obj = LobbySystem::getPlayerCache($this->playerName)) != null) {
                                    $obj->setFallItem($this->getResult());
                                    $particles = $obj->getFallItems();
                                    $particles[] = $this->getResult();
                                    $obj->setFallItems($particles);
                                    $obj->getPlayer()->playSound('random.levelup', 5, 1.0, [$obj->getPlayer()]);
                                    $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-successful-bought', $obj->getPlayer()->getName(), ['#addon' => $this->addon, '#cost' => $this->cost]));
                                }
                            }
                        });
                    }
                }else if($type == self::WING) {
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        $addons = implode(":", $obj->getWings());
                        $addons .= ":$addon";
                        Server::getInstance()->getAsyncPool()->submitTask(new class($player->getName(), $addon, $cost, $addons, MySQLProvider::getMySQLData()) extends AsyncTask {

                            private $playerName;
                            private $addon;
                            private $addons;
                            private $cost;
                            private $mysqlData;

                            public function __construct(string $playerName, string $addon, int $cost, string $addons, $mysqlData)
                            {
                                $this->playerName = $playerName;
                                $this->addon = $addon;
                                $this->addons = $addons;
                                $this->cost = $cost;
                                $this->mysqlData = $mysqlData;
                            }

                            /**
                             * @inheritDoc
                             */
                            public function onRun()
                            {
                                $playerName = $this->playerName;
                                $addon = $this->addon;
                                $addons = $this->addons;
                                $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');

                                $mysqli->query("UPDATE `LobbyPlayer` SET wings='$addons',wing='$addon' WHERE playername='$playerName'");
                                $this->setResult($addon);
                                $mysqli->close();
                            }

                            public function onCompletion(Server $server)
                            {
                                CoinProvider::removeCoins($this->playerName, $this->cost);
                                if (($obj = LobbySystem::getPlayerCache($this->playerName)) != null) {
                                    $obj->setWing($this->getResult());
                                    $particles = $obj->getWings();
                                    $particles[] = $this->getResult();
                                    $obj->setWings($particles);
                                    $obj->getPlayer()->playSound('random.levelup', 5, 1.0, [$obj->getPlayer()]);
                                    $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-successful-bought', $obj->getPlayer()->getName(), ['#addon' => $this->addon, '#cost' => $this->cost]));
                                }
                            }
                        });
                    }
                }else if($type == self::SPECIAL) {
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        $addons = implode(":", $obj->getSpecials());
                        $addons .= ":$addon";
                        Server::getInstance()->getAsyncPool()->submitTask(new class($player->getName(), $addon, $cost, $addons, MySQLProvider::getMySQLData()) extends AsyncTask {

                            private $playerName;
                            private $addon;
                            private $addons;
                            private $cost;
                            private $mysqlData;

                            public function __construct(string $playerName, string $addon, int $cost, string $addons, $mysqlData)
                            {
                                $this->playerName = $playerName;
                                $this->addon = $addon;
                                $this->addons = $addons;
                                $this->cost = $cost;
                                $this->mysqlData = $mysqlData;
                            }

                            /**
                             * @inheritDoc
                             */
                            public function onRun()
                            {
                                $playerName = $this->playerName;
                                $addon = $this->addon;
                                $addons = $this->addons;
                                $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');

                                $mysqli->query("UPDATE `LobbyPlayer` SET specials='$addons',special='$addon' WHERE playername='$playerName'");
                                $mysqli->close();
                                $this->setResult($addon);
                            }

                            public function onCompletion(Server $server)
                            {
                                CoinProvider::removeCoins($this->playerName, $this->cost);
                                if (($obj = LobbySystem::getPlayerCache($this->playerName)) != null) {
                                    $obj->setSpecial($this->getResult());
                                    $particles = $obj->getSpecials();
                                    $particles[] = $this->getResult();
                                    $obj->setSpecials($particles);
                                    $obj->getPlayer()->playSound('random.levelup', 5, 1.0, [$obj->getPlayer()]);
                                    $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-successful-bought', $obj->getPlayer()->getName(), ['#addon' => $this->addon, '#cost' => $this->cost]));
                                }
                            }
                        });
                    }
                }else if($type === self::WALKING_BLOCKS) {
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        $addons = implode(":", $obj->getWalkingBlocks());
                        $addons .= ":$addon";
                        Server::getInstance()->getAsyncPool()->submitTask(new class($player->getName(), $addon, $cost, $addons, MySQLProvider::getMySQLData()) extends AsyncTask {

                            private $playerName;
                            private $addon;
                            private $addons;
                            private $cost;
                            private $mysqlData;

                            public function __construct(string $playerName, string $addon, int $cost, string $addons, $mysqlData)
                            {
                                $this->playerName = $playerName;
                                $this->addon = $addon;
                                $this->addons = $addons;
                                $this->cost = $cost;
                                $this->mysqlData = $mysqlData;
                            }

                            /**
                             * @inheritDoc
                             */
                            public function onRun()
                            {
                                $playerName = $this->playerName;
                                $addon = $this->addon;
                                $addons = $this->addons;
                                $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');

                                $mysqli->query("UPDATE `LobbyPlayer` SET walkingblocks='$addons',walkingblock='$addon' WHERE playername='$playerName'");
                                $mysqli->close();
                                $this->setResult($addon);
                            }

                            public function onCompletion(Server $server)
                            {
                                CoinProvider::removeCoins($this->playerName, $this->cost);
                                if (($obj = LobbySystem::getPlayerCache($this->playerName)) != null) {
                                    $obj->setWalkingBlock($this->getResult());
                                    $particles = $obj->getWalkingBlocks();
                                    $particles[] = $this->getResult();
                                    $obj->setWalkingBlocks($particles);
                                    $obj->getPlayer()->playSound('random.levelup', 5, 1.0, [$obj->getPlayer()]);
                                    $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-successful-bought', $obj->getPlayer()->getName(), ['#addon' => $this->addon, '#cost' => $this->cost]));
                                }
                            }
                        });
                    }
                }
            }
        }, TextFormat::GREEN."Sure", TextFormat::RED."Cancel");
    }
}