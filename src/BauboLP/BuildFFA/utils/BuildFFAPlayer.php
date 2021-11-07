<?php


namespace BauboLP\BuildFFA\utils;


use BauboLP\BuildFFA\BuildFFA;
use BauboLP\BuildFFA\provider\GameProvider;
use BauboLP\BuildFFA\provider\ItemProvider;
use baubolp\core\player\RyzerPlayerProvider;
use baubolp\core\provider\LanguageProvider;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class BuildFFAPlayer
{
    /** @var Player */
    private $player;
    /** @var string */
    private $killer;
    /** @var int */
    private $killStreak;
    /** @var null|string */
    private $invSort;

    /** @var null|int  */
    private $voteKit = null;
    /** @var null|string  */
    private $voteMap = null;
    /** @var int  */
    private $kills = 0;
    /** @var int  */
    private $deaths = 0;
    /** @var bool */
    private $sort;
    /** @var int  */
    private $combo = 0;
    private $lastHit;
    private $cooldowns;
    /** @var array */
    private $invSorts;

    public function __construct(Player $player)
    {
        $this->player = $player;
        $this->cooldowns = ['ep' => null, 'rp' => null];
        $this->invSort = null;
        //$this->invSortAsArray = [0, 1, 2, 8, 4];
        //DEFAULT SORTS
        $this->invSorts[Kits::RUSHER] = ['sword' => 0, 'stick' => 1, 'blocks' => 2, 'webs' => 8, 'kit-item' => 4, 'rp' => 5, 'ep' => 6];
        $this->invSorts[Kits::SNOWBALL] = ['sword' => 0, 'stick' => 1, 'blocks' => 2, 'webs' => 8, 'kit-item' => 4, 'rp' => 5, 'ep' => 6];
        $this->invSorts[Kits::TNT] = ['sword' => 0, 'stick' => 1, 'blocks' => 2, 'webs' => 8, 'kit-item' => 4, 'rp' => 5, 'ep' => 6];
        $this->invSorts[Kits::BASEDEF] = ['sword' => 0, 'stick' => 1, 'blocks' => 2, 'webs' => 8, 'kit-item' => 4, 'rp' => 5, 'ep' => 6];
        $this->invSorts[Kits::SPAMMER] = ['sword' => 0, 'stick' => 1, 'blocks' => 2, 'webs' => 8, 'kit-item' => 4, 'rp' => 5, 'ep' => 6];

        $this->setSort(false);
        $this->resetKiller();
        $this->resetKillStreak();
        $this->resetCombo();
        $this->resetCombo();
    }

    /**
     * @return \pocketmine\Player
     */
    public function getPlayer(): \pocketmine\Player
    {
        return $this->player;
    }

    /**
     * @return string
     */
    public function getKiller(): string
    {
        return $this->killer;
    }

    /**
     * @param string $killer
     */
    public function setKiller(string $killer): void
    {
        $this->killer = $killer;
    }

    public function resetKiller()
    {
        $this->killer = $this->player->getName();
    }

    /**
     * @return int
     */
    public function getKillStreak(): int
    {
        return $this->killStreak;
    }

    /**
     * @param int $killStreak
     */
    public function setKillStreak(int $killStreak): void
    {
        $this->killStreak = $killStreak;
        $this->checkKillStreak();
    }

    public function checkKillStreak(): void
    {
        $streaks = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100, 105, 110, 115, 120, 125, 130, 135, 140, 145, 150];
        if(in_array($this->killStreak, $streaks)) {
            $name = $this->getPlayer()->getName();
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->sendActionBarMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('bffa-player-killstreak', $player->getName(), ['#playername' => $name, '#streak' => $this->killStreak]));
            }
        }
    }

    public function resetKillStreak(): void
    {
        $this->setKillStreak(0);
    }

    /**
     * @param string|null $invSort - SWORD:STICK:BLOCKS:WEBS:KIT-ITEM:RP:EP
     */
    public function setInvSort(?string $invSort): void
    {
        $this->invSort = $invSort;
    }

    /**
     * @return string|null - SWORD:STICK:BLOCKS:WEBS:KIT-ITEM:RP:EP
     */
    public function getInvSort(): ?string
    {
        return $this->invSort;
    }

    /**
     * @param int|null $voteKit
     */
    public function setVoteKit($voteKit): void
    {
        $this->voteKit = $voteKit;
    }

    /**
     * @return null|int
     */
    public function getVoteKit()
    {
        return $this->voteKit;
    }

    /**
     * @param string|null $voteMap
     */
    public function setVoteMap(?string $voteMap): void
    {
        $this->voteMap = $voteMap;
    }

    /**
     * @return string|null
     */
    public function getVoteMap(): ?string
    {
        return $this->voteMap;
    }

    /**
     * @return int
     */
    public function getDeaths(): int
    {
        return $this->deaths;
    }

    /**
     * @param int $deaths
     */
    public function setDeaths(int $deaths): void
    {
        $this->deaths = $deaths;
    }

    /**
     * @param int $kills
     */
    public function setKills(int $kills): void
    {
        $this->kills = $kills;
    }

    /**
     * @return int
     */
    public function getKills(): int
    {
        return $this->kills;
    }

    /**
     * @param int $count
     */
    public function addKill(int $count = 1)
    {
        self::setKills($this->getKills() + $count);
        $this->getPlayer()->getInventory()->removeItem(Item::get(Item::SNOWBALL));
        if(GameProvider::getKit() === Kits::SNOWBALL) {
            $invSort = $this->getInvSorts()[Kits::SNOWBALL];

            $slot = $invSort["kit-item"];
            $this->getPlayer()->getInventory()->setItem($slot, Item::get(Item::SNOWBALL, 0, 16)->setCustomName(TextFormat::GOLD."Snowballs"));
        }
    }
    /**
     * @param int $count
     */
    public function addDeath(int $count = 1)
    {
        self::setDeaths($this->getDeaths() + $count);
    }

    /**
     * @return string
     */
    public function calculateKd()
    {
        if($this->deaths == 0) return $this->kills.".00";
        if($this->kills == 0) return "0.00";

        return number_format($this->kills / $this->deaths, 2);
    }

    public function teleportToSpawn(): void
    {
        if(!Server::getInstance()->isLevelLoaded(GameProvider::getMap())) {
            Server::getInstance()->loadLevel(GameProvider::getMap());
            Server::getInstance()->getLevelByName(GameProvider::getMap())->setTime(6000);
            Server::getInstance()->getLevelByName(GameProvider::getMap())->stopTime();
        }

        $this->getPlayer()->teleport($this->getPlayer()->getServer()->getLevelByName(GameProvider::getMap())->getSafeSpawn());

    }

    public function giveItems()
    {
        $protection = Enchantment::getEnchantment(Enchantment::PROTECTION);
        $unbr = Enchantment::getEnchantment(Enchantment::UNBREAKING);
        $sharpness = Enchantment::getEnchantment(Enchantment::SHARPNESS);
        $effi = Enchantment::getEnchantment(Enchantment::EFFICIENCY);
        $knockback = Enchantment::getEnchantment(Enchantment::KNOCKBACK);
        $infinity = Enchantment::getEnchantment(Enchantment::INFINITY);

        ItemProvider::clearAllInvs($this->getPlayer());

        $helm = Item::get(Item::LEATHER_CAP, 0, 1)->setCustomName(TextFormat::GOLD."Gucci Cap");
        $chestplate = Item::get(Item::CHAIN_CHESTPLATE, 0, 1)->setCustomName(TextFormat::GOLD."Nike Hoodie");
        $leggings = Item::get(Item::LEATHER_LEGGINGS, 0, 1)->setCustomName(TextFormat::GOLD."Second Hand Hose");
        $boots = Item::get(Item::LEATHER_BOOTS, 0, 1)->setCustomName(TextFormat::GOLD."Adidas Sneaker");

        $helm->setUnbreakable(true);
        $chestplate->setUnbreakable(true);
        $leggings->setUnbreakable(true);
        $boots->setUnbreakable(true);
        $enderPearl = Item::get(Item::ENDER_PEARL, 0, 1)->setCustomName(TextFormat::GREEN."EnderPearl");
        $rp = Item::get(Item::BLAZE_ROD, 0, 1)->setCustomName(TextFormat::GREEN."Rettungsplattform");

        $ainv = $this->getPlayer()->getArmorInventory();
        $inv = $this->getPlayer()->getInventory();

        $helm->addEnchantment(new EnchantmentInstance($protection, 1));
        $chestplate->addEnchantment(new EnchantmentInstance($protection, 2));
        $leggings->addEnchantment(new EnchantmentInstance($protection, 1));
        $boots->addEnchantment(new EnchantmentInstance($protection, 1));

        $helm->addEnchantment(new EnchantmentInstance($unbr, 20));
        $chestplate->addEnchantment(new EnchantmentInstance($unbr, 20));
        $leggings->addEnchantment(new EnchantmentInstance($unbr, 20));
        $boots->addEnchantment(new EnchantmentInstance($unbr, 20));

        $ainv->setHelmet($helm);
        $ainv->setBoots($boots);
        $ainv->setChestplate($chestplate);
        $ainv->setLeggings($leggings);

        $sort = $this->getInvSorts()[GameProvider::getKit()];
        $inv->setItem($sort["rp"], $rp);
        $inv->setItem($sort["ep"], $enderPearl);

        $blocks = Item::get(Item::RED_SANDSTONE, 0, 64)->setCustomName(TextFormat::GOLD."Bausteine");
        if(GameProvider::getKit() == Kits::RUSHER) {
            $stick = Item::get(Item::STICK, 0, 1)->setCustomName(TextFormat::GOLD."Knüppel");
            $sword = Item::get(Item::GOLD_SWORD, 0, 1)->setCustomName(TextFormat::GOLD."Machete");
            $sword->setUnbreakable(true);
            $webs = Item::get(Item::WEB, 0, 3)->setCustomName(TextFormat::GOLD."Web");
            $pickaxe = Item::get(Item::IRON_PICKAXE, 0, 1)->setCustomName(TextFormat::GOLD."Spitzhacke");

            $stick->addEnchantment(new EnchantmentInstance($knockback, 1));
            $sword->addEnchantment(new EnchantmentInstance($sharpness, 1));
            $sword->addEnchantment(new EnchantmentInstance($unbr, 20));
            $pickaxe->addEnchantment(new EnchantmentInstance($effi, 2));
            $pickaxe->addEnchantment(new EnchantmentInstance($unbr, 20));

           $inv->setItem($sort["sword"], $sword);
           $inv->setItem($sort["stick"], $stick);
           $inv->setItem($sort["blocks"], $blocks);
           $inv->setItem($sort["kit-item"], $pickaxe);
           $inv->setItem($sort["webs"], $webs);
        }else if(GameProvider::getKit() == Kits::SPAMMER) {
            $stick = Item::get(Item::STICK, 0, 1)->setCustomName(TextFormat::GOLD."Knüppel");
            $sword = Item::get(Item::GOLD_SWORD, 0, 1)->setCustomName(TextFormat::GOLD."Machete");
            $sword->setUnbreakable(true);

            $webs = Item::get(Item::WEB, 0, 3)->setCustomName(TextFormat::GOLD."Web");
            $bow = Item::get(Item::BOW, 0, 1)->setCustomName(TextFormat::GOLD."Bogen");
            $arrow = Item::get(Item::ARROW, 0, 1)->setCustomName(TextFormat::GOLD."Armor's Pfeil <3");

            $stick->addEnchantment(new EnchantmentInstance($knockback, 1));
            $sword->addEnchantment(new EnchantmentInstance($sharpness, 1));
            $sword->addEnchantment(new EnchantmentInstance($unbr, 20));
            $bow->addEnchantment(new EnchantmentInstance($unbr, 20));
            $bow->addEnchantment(new EnchantmentInstance($infinity, 1));

            $inv->setItem($sort["sword"], $sword);
            $inv->setItem($sort["stick"], $stick);
            $inv->setItem($sort["blocks"], $blocks);
            $inv->setItem($sort["kit-item"], $bow);
            $inv->setItem($sort["webs"], $webs);

            $inv->setItem(16, $arrow);
        }else if(GameProvider::getKit() == Kits::BASEDEF) {
            $stick = Item::get(Item::STICK, 0, 1)->setCustomName(TextFormat::GOLD."Knüppel");
            $sword = Item::get(Item::GOLD_SWORD, 0, 1)->setCustomName(TextFormat::GOLD."Machete");
            $sword->setUnbreakable(true);

            $webs = Item::get(Item::WEB, 0, 3)->setCustomName(TextFormat::GOLD."Web");
            $rod = Item::get(Item::FISHING_ROD, 0, 1)->setCustomName(TextFormat::GOLD."Angel");
            $rod->setUnbreakable(true);


            $stick->addEnchantment(new EnchantmentInstance($knockback, 1));
            $sword->addEnchantment(new EnchantmentInstance($sharpness, 1));
            $sword->addEnchantment(new EnchantmentInstance($unbr, 20));

            $inv->setItem($sort["sword"], $sword);
            $inv->setItem($sort["stick"], $stick);
            $inv->setItem($sort["blocks"], $blocks);
            $inv->setItem($sort["kit-item"], $rod);
            $inv->setItem($sort["webs"], $webs);
        }else if(GameProvider::getKit() == Kits::SNOWBALL) {
            $stick = Item::get(Item::STICK, 0, 1)->setCustomName(TextFormat::GOLD."Knüppel");
            $sword = Item::get(Item::GOLD_SWORD, 0, 1)->setCustomName(TextFormat::GOLD."Machete");
            $sword->setUnbreakable(true);

            $webs = Item::get(Item::WEB, 0, 3)->setCustomName(TextFormat::GOLD."Web");
            $snowballs = Item::get(Item::SNOWBALL, 0, 16)->setCustomName(TextFormat::GOLD."Snowballs");

            $stick->addEnchantment(new EnchantmentInstance($knockback, 1));
            $sword->addEnchantment(new EnchantmentInstance($sharpness, 1));
            $sword->addEnchantment(new EnchantmentInstance($unbr, 20));

            $inv->setItem($sort["sword"], $sword);
            $inv->setItem($sort["stick"], $stick);
            $inv->setItem($sort["blocks"], $blocks);
            $inv->setItem($sort["kit-item"], $snowballs);
            $inv->setItem($sort["webs"], $webs);
        }else if(GameProvider::getKit() == Kits::TNT) {
            $stick = Item::get(Item::STICK, 0, 1)->setCustomName(TextFormat::GOLD."Knüppel");
            $sword = Item::get(Item::GOLD_SWORD, 0, 1)->setCustomName(TextFormat::GOLD."Machete");
            $sword->setUnbreakable(true);

            $webs = Item::get(Item::WEB, 0, 1)->setCustomName(TextFormat::GOLD."Web");
            $tnt = Item::get(Item::TNT, 0, 1)->setCustomName(TextFormat::GOLD."Sprengkörper");

            $stick->addEnchantment(new EnchantmentInstance($knockback, 1));
            $sword->addEnchantment(new EnchantmentInstance($sharpness, 1));
            $sword->addEnchantment(new EnchantmentInstance($unbr, 20));

            $inv->setItem($sort["sword"], $sword);
            $inv->setItem($sort["stick"], $stick);
            $inv->setItem($sort["blocks"], $blocks);
            $inv->setItem($sort["kit-item"], $tnt);
            $inv->setItem($sort["webs"], $webs);
        }
    }

    /**
     * @return bool
     */
    public function isSort(): bool
    {
        return $this->sort;
    }

    /**
     * @param bool $sort
     */
    public function setSort(bool $sort): void
    {
        $this->sort = $sort;
    }


    public function updateScoreboard(): void
    {
        ScoreBoard::rmScoreboard($this->getPlayer(), "BuildFFA");
        ScoreBoard::createScoreboard($this->getPlayer(), TextFormat::WHITE.TextFormat::BOLD."RyZerBE", "BuildFFA");
        ScoreBoard::setScoreboardEntry($this->getPlayer(), 1, "", "BuildFFA");
        ScoreBoard::setScoreboardEntry($this->getPlayer(), 2, TextFormat::WHITE."Kills:", "BuildFFA");
        ScoreBoard::setScoreboardEntry($this->getPlayer(), 3, TextFormat::DARK_GRAY."» ".TextFormat::AQUA.$this->getKills(), "BuildFFA");
        ScoreBoard::setScoreboardEntry($this->getPlayer(), 4, "     ", "BuildFFA");
        ScoreBoard::setScoreboardEntry($this->getPlayer(), 5, TextFormat::WHITE."Deaths:", "BuildFFA");
        ScoreBoard::setScoreboardEntry($this->getPlayer(), 6, TextFormat::DARK_GRAY."» ".TextFormat::AQUA.$this->getDeaths(), "BuildFFA");
        ScoreBoard::setScoreboardEntry($this->getPlayer(), 7, "  ", "BuildFFA");
        ScoreBoard::setScoreboardEntry($this->getPlayer(), 8, TextFormat::WHITE."K/D:", "BuildFFA");
        ScoreBoard::setScoreboardEntry($this->getPlayer(), 9, TextFormat::DARK_GRAY."» ".TextFormat::AQUA.$this->calculateKd(), "BuildFFA");
    }

    /**
     * @param int $combo
     */
    public function addCombo(int $combo = 1)
    {
        $this->combo = $this->combo + $combo;
        $this->lastHit = time() + 1;
    }

    public function resetCombo()
    {
        $this->combo = 0;
    }

    /**
     * @return mixed
     */
    public function getLastHit()
    {
        return $this->lastHit;
    }

    /**
     * @return int
     */
    public function getCombo(): int
    {
        return $this->combo;
    }

    /**
     * @return array
     */
    public function getCooldowns()
    {
        return $this->cooldowns;
    }

    /**
     * @param int $sec
     */
    public function addEpCooldown(int $sec = 12)
    {
        $this->cooldowns['ep'] = time() + $sec;
    }

    /**
     * @param int $sec
     */
    public function addRPCooldown(int $sec = 12)
    {
        $this->cooldowns['rp'] = time() + $sec;
    }

    public function setCooldown($index, $cooldown)
    {
        $this->cooldowns[$index] = $cooldown;
    }

    /**
     * @return array
     */
    public function getInvSorts(): array
    {
        return $this->invSorts;
    }

    /**
     * @param array $invSorts
     */
    public function setInvSorts(array $invSorts): void
    {
        $this->invSorts = $invSorts;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if(($obj = RyzerPlayerProvider::getRyzerPlayer($this->getPlayer()->getName())) != null)
            return $obj->getName();

        return $this->getPlayer()->getName();
    }
}