<?php


namespace BauboLP\LobbySystem\Utils;


use BauboLP\Core\Player\RyzerPlayerProvider;
use BauboLP\Core\Provider\CoinProvider;
use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\Core\Provider\MySQLProvider;
use BauboLP\Core\Provider\RankProvider;
use BauboLP\Core\Ryzer;
use BauboLP\Core\Utils\HoloGram;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\AnimationProvider;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class LobbyPlayer
{
    /** @var \pocketmine\Player */
    private $player;
    /** @var bool  */
    private $animation = true;
    /** @var bool */
    private $build = false;
    /** @var bool */
    private $fly = false;

    /** @var array  */
    private $particles = [];
    /** @var null|string */
    private $particle = null;
    /** @var null|string */
    private $pet = null;
    /** @var array  */
    private $pets = [];
    /** @var array  */
    private $wings = [];
    /** @var null|string  */
    private $wing = null;
    /** @var null|\BauboLP\LobbySystem\Utils\Wing */
    private $wingObject = null;
    /** @var array  */
    private $specials = [];
    /** @var null|string */
    private $special = null;
    /** @var array  */
    private $fallItems = [];
    /** @var null|string  */
    private $fallItem = null;
    /** @var array */
    private $walkingBlocks = [];
    /** @var string|null */
    private $walkingBlock = null;
    /** @var bool  */
    private $addonsActivated = true;

    /** @var null|int */
    private $dailyCoins = null;
    /** @var null|int */
    private $dailyLotto = null;
    /** @var null|int */
    private $dailyCoinBomb = null;
    /** @var int  */
    private $loginStreak = 0;
    /** @var null|int */
    private $lastStreakDay = null;
    /** @var null|int */
    private $nextStreakDay = null;
    /** @var int  */
    private $tickets = 0;
    /** @var int  */
    private $coinBombs = 0;
    /** @var bool  */
    private $goal = false;
    /** @var bool  */
    private $isInGame = false;
    /** @var bool  */
    private $shield = false;
    /** @var array  */
    private $lottoWin = [];
    /** @var \pocketmine\math\Vector3  */
    private $lastPos;
    /** @var int  */
    private $posChecks = 0;
    /** @var \pocketmine\math\Vector3|null */
    private $nearSign;
    /** @var bool  */
    private $playJumpAndRun = false;
    /** @var int */
    private $jumpAndRunTime = 0.0;
    /** @var string */
    private $jumpAndRunTimeString = "0:00";
    /** @var string */
    private $bestJumpAndRunTime = "0:00";
    /** @var \BauboLP\Core\Utils\HoloGram|null */
    private $jarHolo;

    public function __construct(Player $player)
    {
        $this->jarHolo = null;
        $this->player = $player;
        $this->lastPos = $player->asVector3();
    }

    /**
     * @return \pocketmine\Player
     */
    public function getPlayer(): \pocketmine\Player
    {
        return $this->player;
    }

    /**
     * @return bool
     */
    public function willAnimation(): bool
    {
        return $this->animation;
    }

    public function sendJoinAnimation(): void
    {
       AnimationProvider::addPlayerToAnimation($this->getPlayer()->getName());
    }

    /**
     * @return bool
     */
    public function isBuildModeActivated(): bool
    {
        return $this->build;
    }

    /**
     * @param bool $build
     */
    public function setBuild(bool $build): void
    {
        $this->build = $build;
    }

    /**
     * @return bool
     */
    public function isFlyActivated(): bool
    {
        return $this->fly;
    }

    /**
     * @param bool $fly
     */
    public function setFly(bool $fly): void
    {
        $this->fly = $fly;
    }

    /**
     * @return string|null
     */
    public function getParticle(): ?string
    {
        return $this->particle;
    }

    /**
     * @return array
     */
    public function getParticles(): array
    {
        return $this->particles;
    }

    /**
     * @return string|null
     */
    public function getPet(): ?string
    {
        return $this->pet;
    }

    /**
     * @return array
     */
    public function getPets(): array
    {
        return $this->pets;
    }

    /**
     * @return string|null
     */
    public function getSpecial(): ?string
    {
        return $this->special;
    }

    /**
     * @return array
     */
    public function getSpecials(): array
    {
        return $this->specials;
    }

    /**
     * @return string|null
     */
    public function getWing(): ?string
    {
        return $this->wing;
    }

    /**
     * @return array
     */
    public function getWings(): array
    {
        return $this->wings;
    }

    /**
     * @param bool $animation
     */
    public function setAnimation(bool $animation): void
    {
        $this->animation = $animation;
    }

    /**
     * @param string|null $particle
     */
    public function setParticle(?string $particle): void
    {
        $this->particle = $particle;
        Server::getInstance()->getAsyncPool()->submitTask(new class($this->getPlayer()->getName(), $particle, MySQLProvider::getMySQLData()) extends AsyncTask{

            private $playerName;
            private $particle;
            private $mysqlData;

            public function __construct(string $playerName, ?string $particle, array $mysqlProvider)
            {
                $this->particle = $particle;
                $this->playerName = $playerName;
                $this->mysqlData = $mysqlProvider;
            }

            /**
             * @inheritDoc
             */
            public function onRun()
            {
                $playerName = $this->playerName;
                $particle = $this->particle;
                if($particle == null) $particle = "";

                $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');
                $mysqli->query("UPDATE LobbyPlayer SET particle='$particle' WHERE playername='$playerName'");
                $mysqli->close();
            }
        });
    }

    /**
     * @param array $particles
     */
    public function setParticles(array $particles): void
    {
        $this->particles = $particles;
    }


    /**
     * @param string|null $special
     */
    public function setSpecial(?string $special): void
    {
        $this->special = $special;
        Ryzer::getMysqlProvider()->exec(new class($this->getPlayer()->getName(), $special, MySQLProvider::getMySQLData()) extends AsyncTask{

            private $playerName;
            private $particle;
            private $mysqlData;

            public function __construct(string $playerName, ?string $particle, array $mysqlProvider)
            {
                $this->particle = $particle;
                $this->playerName = $playerName;
                $this->mysqlData = $mysqlProvider;
            }

            /**
             * @inheritDoc
             */
            public function onRun()
            {
                $playerName = $this->playerName;
                $particle = $this->particle;
                if($particle == null) $particle = "";

                $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');
                $mysqli->query("UPDATE LobbyPlayer SET special='$particle' WHERE playername='$playerName'");
                $mysqli->close();
            }
        });
    }

    /**
     * @param array $specials
     */
    public function setSpecials(array $specials): void
    {
        $this->specials = $specials;
    }

    /**
     * @param string|null $wing
     */
    public function setWing(?string $wing): void
    {
        $this->wing = $wing;
        if($wing != null)
            $this->loadWingObject();
        else
            $this->wingObject = null;

        Server::getInstance()->getAsyncPool()->submitTask(new class($this->getPlayer()->getName(), $wing, MySQLProvider::getMySQLData()) extends AsyncTask{

            private $playerName;
            private $particle;
            private $mysqlData;

            public function __construct(string $playerName, ?string $particle, array $mysqlProvider)
            {
                $this->particle = $particle;
                $this->playerName = $playerName;
                $this->mysqlData = $mysqlProvider;
            }

            /**
             * @inheritDoc
             */
            public function onRun()
            {
                $playerName = $this->playerName;
                $particle = $this->particle;
                if($particle == null) $particle = "";

                $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');
                $mysqli->query("UPDATE LobbyPlayer SET wing='$particle' WHERE playername='$playerName'");
                $mysqli->close();
            }
        });
    }

    /**
     * @param array $wings
     */
    public function setWings(array $wings): void
    {
        $this->wings = $wings;
    }

    /**
     * @param string|null $fallItem
     */
    public function setFallItem(?string $fallItem): void
    {
        $this->fallItem = $fallItem;
        Server::getInstance()->getAsyncPool()->submitTask(new class($this->getPlayer()->getName(), $fallItem, MySQLProvider::getMySQLData()) extends AsyncTask{

            private $playerName;
            private $particle;
            private $mysqlData;

            public function __construct(string $playerName, ?string $particle, array $mysqlProvider)
            {
                $this->particle = $particle;
                $this->playerName = $playerName;
                $this->mysqlData = $mysqlProvider;
            }

            /**
             * @inheritDoc
             */
            public function onRun()
            {
                $playerName = $this->playerName;
                $particle = $this->particle;
                if($particle == null) $particle = "";

                $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');
                $mysqli->query("UPDATE LobbyPlayer SET fallitem='$particle' WHERE playername='$playerName'");
                $mysqli->close();
            }
        });
    }

    /**
     * @param array $fallItems
     */
    public function setFallItems(array $fallItems): void
    {
        $this->fallItems = $fallItems;
    }

    /**
     * @return string|null
     */
    public function getFallItem(): ?string
    {
        return $this->fallItem;
    }

    /**
     * @return array
     */
    public function getFallItems(): array
    {
        return $this->fallItems;
    }

    /**
     * @param string $particle
     * @return bool
     */
    public function haveBoughtParticle(string $particle)
    {
        return in_array($particle, $this->particles);
    }
    /**
     * @param string $fallItem
     * @return bool
     */
    public function haveBoughtFallItem(string $fallItem)
    {
        return in_array($fallItem, $this->fallItems);
    }
    /**
     * @param string $wing
     * @return bool
     */
    public function haveBoughtWing(string $wing)
    {
        return in_array($wing, $this->wings);
    }
    /**
     * @param string $special
     * @return bool
     */
    public function haveBoughtSpecial(string $special)
    {
        return in_array($special, $this->specials);
    }

    /**
     * @param bool $addonsActivated
     */
    public function setAddonsActivated(bool $addonsActivated): void
    {
        $this->addonsActivated = $addonsActivated;
    }

    /**
     * @return bool
     */
    public function isAddonsActivated(): bool
    {
        return $this->addonsActivated && $this->getPosChecks() < 300;
    }

    /**
     * @return int|null
     */
    public function getDailyCoinBomb(): ?int
    {
        return $this->dailyCoinBomb;
    }

    /**
     * @return int|null
     */
    public function getDailyCoins(): ?int
    {
        return $this->dailyCoins;
    }

    /**
     * @return int|null
     */
    public function getDailyLotto(): ?int
    {
        return $this->dailyLotto;
    }

    /**
     * @param int|null $dailyCoinBomb
     */
    public function setDailyCoinBomb(?int $dailyCoinBomb): void
    {
        $this->dailyCoinBomb = $dailyCoinBomb;
    }

    /**
     * @param int|null $dailyCoins
     */
    public function setDailyCoins(?int $dailyCoins): void
    {
        $this->dailyCoins = $dailyCoins;
    }

    /**
     * @param int|null $dailyLotto
     */
    public function setDailyLotto(?int $dailyLotto): void
    {
        $this->dailyLotto = $dailyLotto;
    }

    /**
     * @return int|null
     */
    public function getLastStreakDay(): ?int
    {
        return $this->lastStreakDay;
    }

    /**
     * @return int
     */
    public function getLoginStreak(): int
    {
        return $this->loginStreak;
    }

    /**
     * @return int|null
     */
    public function getNextStreakDay(): ?int
    {
        return $this->nextStreakDay;
    }

    /**
     * @param int|null $lastStreakDay
     */
    public function setLastStreakDay(?int $lastStreakDay): void
    {
        $this->lastStreakDay = $lastStreakDay;
    }

    /**
     * @param int $loginStreak
     */
    public function setLoginStreak(int $loginStreak): void
    {
        $this->loginStreak = $loginStreak;
    }

    /**
     * @param int|null $nextStreakDay
     */
    public function setNextStreakDay(?int $nextStreakDay): void
    {
        $this->nextStreakDay = $nextStreakDay;
    }

    public function checkLoginStreak()
    {
        $now = time();
        if (date("Y-m-d", $now) != date("Y-m-d", $this->getLastStreakDay())) {
            if (date("Y-m-d", $this->getNextStreakDay()) == date("Y-m-d", $now)) {
                $this->setLoginStreak($this->getLoginStreak() + 1);
                $this->setNextStreakDay(strtotime("next day"));
                $this->setLastStreakDay($now);
                if (in_array($this->getLoginStreak(), [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100, 105, 110, 115, 120, 125, 130, 135, 140, 145, 150, 155, 160, 165, 170, 175, 180, 185, 190, 195, 200])) {
                    if ($this->getPlayer() != null) {
                        CoinProvider::addCoins($this->getPlayer()->getName(), 1000);
                        $this->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-loginstreak-get-coins', $this->getPlayer()->getName(), ['#coins' => 1000]));
                    }
                }

                Server::getInstance()->getAsyncPool()->submitTask(new class($this->getPlayer()->getName(), MySQLProvider::getMySQLData(), $this->getLoginStreak(), $this->getNextStreakDay(), $this->getLastStreakDay()) extends AsyncTask {

                    private $playerName;
                    private $mysqlData;
                    private $loginStreak;
                    private $next;
                    private $last;

                    public function __construct(string $playerName, array $mysqlData, int $loginstreak, int $next, int $last)
                    {
                        $this->playerName = $playerName;
                        $this->mysqlData = $mysqlData;
                        $this->loginStreak = $loginstreak;
                        $this->next = $next;
                        $this->last = $last;
                    }

                    /**
                     * @inheritDoc
                     */
                    public function onRun()
                    {
                        $playerName = $this->playerName;
                        $ls = $this->loginStreak;
                        $last = $this->last;
                        $next = $this->next;

                        $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');
                        $mysqli->query("UPDATE LoginStreak SET loginstreak='$ls',nextstreakday='$next',laststreakday='$last' WHERE playername='$playerName'");
                        $mysqli->close();
                    }
                });
            }else {
                $this->resetLoginStreak();
            }
        }
    }

    public function resetLoginStreak(): void
    {
        $this->setLastStreakDay(time());
        $this->setNextStreakDay(strtotime("next day"));
        $this->setLoginStreak(0);
        Server::getInstance()->getAsyncPool()->submitTask(new class($this->getPlayer()->getName(), MySQLProvider::getMySQLData()) extends AsyncTask{

            private $playerName;
            private $mysqlData;

            public function __construct(string $playerName, array $mysqlData)
            {
                $this->playerName = $playerName;
                $this->mysqlData = $mysqlData;
            }

            /**
             * @inheritDoc
             */
            public function onRun()
            {
                $playerName = $this->playerName;
                $now = strtotime("next day");
                $now2 = time();

                $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');
                $mysqli->query("UPDATE LoginStreak SET loginstreak='0',nextstreakday='$now',laststreakday='$now2' WHERE playername='$playerName'");
                $mysqli->close();
            }

            public function onCompletion(Server $server)
            {
                if(($player = $server->getPlayerExact($this->playerName)) != null) {
                    $player->sendMessage(LobbySystem::Prefix.LanguageProvider::getMessageContainer('lobby-loginstreak-reset', $player->getName()));
                }
            }
        });
    }

    /**
     * @return int
     */
    public function getCoinBombs(): int
    {
        return $this->coinBombs;
    }

    /**
     * @return int
     */
    public function getTickets(): int
    {
        return $this->tickets;
    }

    /**
     * @param int $coinBombs
     */
    public function setCoinBombs(int $coinBombs): void
    {
        $this->coinBombs = $coinBombs;
    }

    /**
     * @param int $tickets
     */
    public function setTickets(int $tickets): void
    {
        $this->tickets = $tickets;
    }

    /**
     * @param bool $isInGame
     */
    public function setIsInGame(bool $isInGame): void
    {
        $this->isInGame = $isInGame;
    }

    /**
     * @param bool $goal
     */
    public function setGoal(bool $goal): void
    {
        $this->goal = $goal;
    }

    /**
     * @return bool
     */
    public function isGoal(): bool
    {
        return $this->goal;
    }

    /**
     * @return bool
     */
    public function isInGame(): bool
    {
        return $this->isInGame;
    }

    /**
     * @param bool $shield
     */
    public function setShield(bool $shield): void
    {
        $this->shield = $shield;
    }

    /**
     * @return bool
     */
    public function isShield(): bool
    {
        return $this->shield;
    }

    /**
     * @return array
     */
    public function getLottoWin(): array
    {
        return $this->lottoWin;
    }

    /**
     * @param array $lottoWin
     */
    public function setLottoWin(array $lottoWin): void
    {
        $this->lottoWin = $lottoWin;
    }

    public function addLottoWin(int $coins)
    {
        $this->lottoWin[] = $coins;
    }

    public function updateScoreboard(): void
    {
        $ryzerPlayer = RyzerPlayerProvider::getRyzerPlayer($this->getPlayer()->getName());
        if($ryzerPlayer == null) return;

        Scoreboard::rmScoreboard($this->getPlayer(), "Lobby");
        Scoreboard::createScoreboard($this->getPlayer(), TextFormat::BOLD.TextFormat::WHITE."RyZer".TextFormat::AQUA."BE", "Lobby");
        Scoreboard::setScoreboardEntry($this->getPlayer(), 0, "", "Lobby");
        Scoreboard::setScoreboardEntry($this->getPlayer(), 1, TextFormat::AQUA."Rank", "Lobby");
        Scoreboard::setScoreboardEntry($this->getPlayer(), 2, TextFormat::DARK_GRAY."» ".str_replace("&", TextFormat::ESCAPE, explode(" ", RankProvider::getNameTag($ryzerPlayer->getRank()))[0]), "Lobby");
        Scoreboard::setScoreboardEntry($this->getPlayer(), 3, " ", "Lobby");
        Scoreboard::setScoreboardEntry($this->getPlayer(), 4, TextFormat::AQUA."Coins", "Lobby");
        Scoreboard::setScoreboardEntry($this->getPlayer(), 5, TextFormat::DARK_GRAY."» ".TextFormat::WHITE.$ryzerPlayer->getCoins(), "Lobby");
        Scoreboard::setScoreboardEntry($this->getPlayer(), 6, "  ", "Lobby");
        Scoreboard::setScoreboardEntry($this->getPlayer(), 7, TextFormat::AQUA."PlayTime", "Lobby");
        Scoreboard::setScoreboardEntry($this->getPlayer(), 8, TextFormat::DARK_GRAY."» ".TextFormat::WHITE.$ryzerPlayer->getOnlineTime(), "Lobby");
        Scoreboard::setScoreboardEntry($this->getPlayer(), 9, "     ", "Lobby");
        Scoreboard::setScoreboardEntry($this->getPlayer(), 10, TextFormat::AQUA."Clan", "Lobby");
        Scoreboard::setScoreboardEntry($this->getPlayer(), 11, TextFormat::DARK_GRAY."» ".TextFormat::YELLOW.$ryzerPlayer->getClan().TextFormat::GRAY."[".str_replace("&", TextFormat::ESCAPE, $ryzerPlayer->getClanTag()).TextFormat::GRAY."]", "Lobby");
    }

    /**
     * @return \pocketmine\math\Vector3
     */
    public function getLastPos(): \pocketmine\math\Vector3
    {
        return $this->lastPos;
    }

    /**
     * @param \pocketmine\math\Vector3 $lastPos
     */
    public function setLastPos(\pocketmine\math\Vector3 $lastPos): void
    {
        $this->lastPos = $lastPos;
    }

    /**
     * @return int
     */
    public function getPosChecks(): int
    {
        return $this->posChecks;
    }

    /**
     * @param int $posChecks
     */
    public function setPosChecks(int $posChecks): void
    {
        $this->posChecks = $posChecks;
    }

    /**
     * @return \pocketmine\math\Vector3|null
     */
    public function getNearSign(): ?\pocketmine\math\Vector3
    {
        return $this->nearSign;
    }

    /**
     * @param \pocketmine\math\Vector3|null $nearSign
     */
    public function setNearSign(?\pocketmine\math\Vector3 $nearSign): void
    {
        $this->nearSign = $nearSign;
    }

    /**
     * @return bool
     */
    public function playingJumpAndRun(): bool
    {
        return $this->playJumpAndRun;
    }

    /**
     * @param bool $playJumpAndRun
     */
    public function setPlayingJumpAndRun(bool $playJumpAndRun): void
    {
        $this->playJumpAndRun = $playJumpAndRun;
    }

    public function updateTimer() {
        if($this->jumpAndRunTime == null) {
            $this->jumpAndRunTime = 0.0; //default: 0
        }
        $this->jumpAndRunTimeString = date("i:s", $this->jumpAndRunTime += 0.20);
    }

    public function resetTimer()
    {
        $this->jumpAndRunTime = 0.0;
        $this->jumpAndRunTimeString = "0:00";
    }

    /**
     * @return string
     */
    public function getJumpAndRunTimeString(): string
    {
        if(isset(explode(".", $this->jumpAndRunTime)[1])) return $this->jumpAndRunTimeString.".".explode(".", $this->jumpAndRunTime)[1];

        return $this->jumpAndRunTimeString;
    }

    /**
     * @return string
     */
    public function getBestJumpAndRunTime(): string
    {
        return $this->bestJumpAndRunTime;
    }

    /**
     * @param string $bestJumpAndRunTime
     */
    public function setBestJumpAndRunTime(string $bestJumpAndRunTime): void
    {
        $this->bestJumpAndRunTime = $bestJumpAndRunTime;
    }

    /**
     * @param \BauboLP\Core\Utils\HoloGram|null $jarHolo
     */
    public function setJarHolo(?HoloGram $jarHolo): void
    {
        $this->jarHolo = $jarHolo;
    }

    /**
     * @return \BauboLP\Core\Utils\HoloGram|null
     */
    public function getJarHolo(): ?HoloGram
    {
        return $this->jarHolo;
    }

    public function loadWingObject()
    {
        $wing = $this->getWing();
        if($wing == null || empty(LobbySystem::getWingsData()[$wing]["shape"])) return;
        $data = LobbySystem::getWingsData()[$wing]["shape"];
        $this->wingObject = new Wing($data);
    }

    /**
     * @return \BauboLP\LobbySystem\Utils\Wing|null
     */
    public function getWingObject(): ?Wing
    {
        return $this->wingObject;
    }


    /**
     * @return string|null
     */
    public function getWalkingBlock(): ?string
    {
        return $this->walkingBlock;
    }

    /**
     * @return array
     */
    public function getWalkingBlocks(): array
    {
        return $this->walkingBlocks;
    }

    /**
     * @param string|null $walkingBlock
     */
    public function setWalkingBlock(?string $walkingBlock): void
    {
        $this->walkingBlock = $walkingBlock;
        Server::getInstance()->getAsyncPool()->submitTask(new class($this->getPlayer()->getName(), $walkingBlock, MySQLProvider::getMySQLData()) extends AsyncTask{

            private $playerName;
            private $particle;
            private $mysqlData;

            public function __construct(string $playerName, ?string $particle, array $mysqlProvider)
            {
                $this->particle = $particle;
                $this->playerName = $playerName;
                $this->mysqlData = $mysqlProvider;
            }

            /**
             * @inheritDoc
             */
            public function onRun()
            {
                $playerName = $this->playerName;
                $particle = $this->particle;
                if($particle == null) $particle = "";

                $mysqli = new \mysqli($this->mysqlData['host'] . ':3306', $this->mysqlData['user'], $this->mysqlData['password'], 'Lobby');
                $mysqli->query("UPDATE LobbyPlayer SET walkingblock='$particle' WHERE playername='$playerName'");
                $mysqli->close();
            }
        });
    }

    /**
     * @param array $walkingBlocks
     */
    public function setWalkingBlocks(array $walkingBlocks): void
    {
        $this->walkingBlocks = $walkingBlocks;
    }

    /**
     * @param string $walkingBlock
     * @return bool
     */
    public function haveBoughtWalkingBlock(string $walkingBlock)
    {
        return in_array($walkingBlock, $this->walkingBlocks);
    }
}