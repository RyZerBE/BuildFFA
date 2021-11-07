<?php


namespace BauboLP\BuildFFA\provider;


use BauboLP\BuildFFA\animation\AnimationProvider;
use BauboLP\BuildFFA\animation\type\BlockAnimation;
use BauboLP\BuildFFA\tasks\GameTask;
use BauboLP\BuildFFA\utils\BuildFFAPlayer;
use BauboLP\BuildFFA\utils\Kits;
use pocketmine\math\Vector3;
use pocketmine\Player;

class GameProvider
{
//    public static $maps = ["Gomme-DeathMatch" => ['votes' => 0], "CWBW" => ['votes' => 0], 'Trouble' => ['votes' => 0], 'Saftey' => ['votes' => 0]];

    /** @var array  */
    public static $maps = ["Gomme-DeathMatch" => ['votes' => 0], "CWBW" => ['votes' => 0], 'Trouble' => ['votes' => 0], 'Saftey' => ['votes' => 0]];
    /** @var string  */
    private static $map = "Saftey";

    const VOTE_AREA = "Voting-Area";
    /** @var array  */
    public static $kits = [Kits::RUSHER => ['votes' => 0], Kits::SPAMMER => ['votes' => 0], Kits::BASEDEF => ['votes' => 0], Kits::SNOWBALL => ['votes' => 0]];
    /** @var int  */
    private static $kit = Kits::RUSHER;
    /** @var string  */
    private static $string_timer = "1:01";
    /** @var int|null */
    public static $time = null;
    /** @var bool  */
    private static $pvp = true;
    /** @var bool  */
    private static $voting = false;
    /** @var \BauboLP\BuildFFA\utils\BuildFFAPlayer[}  */
    public static $players = [];
    /** @var array  */
    public static $skip = [];
    /** @var array  */
    public static $placedBlocks = [];
    /** @var array  */
    private static $brokeBlocks = [];
    /** @var array  */
    public static $removeItems = [];
    /** @var bool  */
    public static $isSkipped = false;
    /** @var int  */
    public static $forceDelay = 0;
    /**
     * @return \BauboLP\BuildFFA\utils\BuildFFAPlayer
     */
    public static function getPlayers(): \BauboLP\BuildFFA\utils\BuildFFAPlayer
    {
        return self::$players;
    }

    /**
     * @param string $playerName
     * @return \BauboLP\BuildFFA\utils\BuildFFAPlayer|null
     */
    public static function getBuildFFAPlayer(string $playerName): ?BuildFFAPlayer
    {
        if(!isset(self::$players[$playerName])) return null;

        return self::$players[$playerName];
    }

    /**
     * @param \pocketmine\Player $player
     * @return BuildFFAPlayer
     */
    public static function createPlayer(Player $player): BuildFFAPlayer
    {
        self::$players[$player->getName()] = new BuildFFAPlayer($player);

        return self::$players[$player->getName()];
    }

    /**
     * @param \pocketmine\Player $player
     */
    public static function removePlayer(Player $player): void
    {
        unset(self::$players[$player->getName()]);
    }
    /**
     * @return int
     */
    public static function getKit(): int
    {
        return self::$kit;
    }

    /**
     * @return array
     */
    public static function getKits(): array
    {
        return self::$kits;
    }

    /**
     * @return string
     */
    public static function getMap(): string
    {
        return self::$map;
    }

    /**
     * @return array
     */
    public static function getMaps(): array
    {
        return self::$maps;
    }

    /**
     * @param int $kit
     */
    public static function setKit(int $kit): void
    {
        self::$kit = $kit;
    }

    /**
     * @param array $kits
     */
    public static function setKits(array $kits): void
    {
        self::$kits = $kits;
    }

    /**
     * @param string $map
     */
    public static function setMap(string $map): void
    {
        self::$map = $map;
    }

    /**
     * @param array $maps
     */
    public static function setMaps(array $maps): void
    {
        self::$maps = $maps;
    }

    public static function updateTimer(): void {
        if(self::$time == null) {
            self::$time = time();
        }
        $z = self::$time + 60 * 15;
        $t = abs(time() - $z);

        GameTask::$END_TIME = $t;
        self::$string_timer = date("i:s", $t);
    }

    public static function getTimer(): string
    {
        return self::$string_timer;
    }

    /**
     * @return bool
     */
    public static function isPvP(): bool
    {
        return self::$pvp;
    }

    /**
     * @param bool $pvp
     */
    public static function setPvP(bool $pvp): void
    {
        self::$pvp = $pvp;
    }

    /**
     * @param bool $voting
     */
    public static function setVoting(bool $voting): void
    {
        self::$voting = $voting;
    }

    /**
     * @return bool
     */
    public static function isVoting(): bool
    {
        return self::$voting;
    }

    /**
     * @param string $playerName
     */
    public static function addSkipPlayer(string $playerName): void
    {
        self::$skip[] = $playerName;
    }

    public static function clearSkips(): void
    {
        self::$skip = [];
    }

    /**
     * @return array
     */
    public static function getSkip(): array
    {
        return self::$skip;
    }

    /**
     * @return string
     */
    public static function getVotedArena(): string
    {
        $votes = [];
        foreach (self::getMaps() as $map => $vote) {
            $votes[$map] = $vote["votes"];
        }

        return array_search(max($votes), $votes);
    }

    /**
     * @return int
     */
    public static function getVotedKit(): int
    {
        $votes = [];
        foreach (self::getKits() as $map => $vote) {
            $votes[$map] = $vote["votes"];
        }

        return array_search(max($votes), $votes);
    }

    /**
     * @return array
     */
    public static function getBrokeBlocks(): array
    {
        return self::$brokeBlocks;
    }

    /**
     * @return array
     */
    public static function getPlacedBlocks(): array
    {
        return self::$placedBlocks;
    }

    /**
     * @param string $playerName
     * @param \pocketmine\math\Vector3 $vector3
     * @param bool $animation
     */
    public static function addPlacedBlock(string $playerName, Vector3 $vector3, bool $animation = false)
    {
        $blockPos = "{$vector3->x}:{$vector3->y}:{$vector3->z}";
        #self::$placedBlocks[$blockPos] = ['pos' => $vector3, 'stringPos' => $blockPos, 'player' => $playerName,  'time' => time() + 5, 'count' => 0, 'animation' => $animation];
    }

    /**
     * @param \pocketmine\math\Vector3 $vector3
     */
    public static function addBreakBlock(Vector3 $vector3)
    {
        $blockPos = "{$vector3->x}:{$vector3->y}:{$vector3->z}";
        self::$brokeBlocks[$blockPos] = ['pos' => $vector3, 'time' => time() + 5];
    }

    /**
     * @param \pocketmine\math\Vector3 $vector3
     */
    public static function removePlacedBlock(Vector3 $vector3)
    {
        $blockPos = "{$vector3->x}:{$vector3->y}:{$vector3->z}";
        unset(self::$placedBlocks[$blockPos]);
    }

    /**
     * @param \pocketmine\math\Vector3 $vector3
     */
    public static function removeBrokeBlock(Vector3 $vector3)
    {
        $blockPos = "{$vector3->x}:{$vector3->y}:{$vector3->z}";
        unset(self::$brokeBlocks[$blockPos]);
    }


    public static function resetVotes(): void
    {
        foreach (array_keys(self::$maps) as $key) {
            self::$maps[$key]['votes'] = 0;
        }

        foreach (array_keys(self::$kits) as $key) {
            self::$kits[$key]['votes'] = 0;
        }
    }
}