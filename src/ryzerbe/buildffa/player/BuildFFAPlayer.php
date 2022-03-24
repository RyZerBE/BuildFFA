<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\player;

use mysqli;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use ryzerbe\buildffa\BuildFFA;
use ryzerbe\buildffa\game\GameManager;
use ryzerbe\buildffa\game\kit\Kit;
use ryzerbe\buildffa\game\kit\KitManager;
use ryzerbe\buildffa\game\map\MapManager;
use ryzerbe\buildffa\game\perks\Perk;
use ryzerbe\buildffa\game\safezone\SafeZoneManager;
use ryzerbe\core\language\LanguageProvider;
use ryzerbe\core\player\PMMPPlayer;
use ryzerbe\core\player\RyZerPlayer;
use ryzerbe\core\player\RyZerPlayerProvider;
use ryzerbe\core\provider\CoinProvider;
use ryzerbe\core\util\async\AsyncExecutor;
use ryzerbe\core\util\cache\CacheTrait;
use ryzerbe\core\util\customitem\CustomItem;
use ryzerbe\core\util\ItemUtils;
use ryzerbe\core\util\scoreboard\Scoreboard;
use function array_filter;
use function count;
use function intval;
use function json_decode;
use function json_encode;
use function round;

class BuildFFAPlayer {
    use CacheTrait;

    public const KEY_LAST_DAMAGER = "last_damager";
    public const KEY_LAST_KILLER = "last_killer";
    public const KEY_LAST_KILL = "last_kill";

    public const TIME_LAST_DAMAGER = 200;
    public const TIME_LAST_KILLER = 70;
    public const TIME_LAST_KILL = 70;

    protected bool $inSafeZone = false;

    protected bool $sortsInventory = false;
    protected ?Kit $sortingKit = null;

    /** @var int[][] */
    protected array $items = [];

    protected int $deaths = 0;
    protected int $kills = 0;
    protected int $killStreak = 0;

    /** @var CustomItem[]  */
    protected array $givePerks = [];

    protected Scoreboard $scoreboard;

    public function __construct(
        protected PMMPPlayer $player
    ){
        $this->load();
        $this->scoreboard = new Scoreboard(RyZerPlayerProvider::getRyzerPlayer($this->getPlayer()), TextFormat::RED.TextFormat::BOLD."BuildFFA");
    }

    public function load(): void {
        $playername = $this->player->getName();
        AsyncExecutor::submitMySQLAsyncTask("BuildFFA", function(mysqli $mysqli) use ($playername): array {
            $items = [];
            $query = $mysqli->query("SELECT * FROM inventory_sort WHERE playername='$playername'");
            while($row = $query->fetch_assoc()) {
                $decodedItems = json_decode($row["inventory"], true);
                if($decodedItems === null) {
                    continue;
                }
                $kit = $row["kit"];
                foreach($decodedItems as $item => $slot) {
                    $items[$kit][$item] = intval($slot);
                }
            }
            return $items;
        }, function(Server $server, array $items): void {
            if(!$this->getPlayer()->isConnected()) return;
            $this->items = $items;

            GameManager::getBossbar()->showTo($this->getPlayer());
        });
    }

    public function unload(): void {
        foreach(MapManager::getMaps() as $map) {
            $map->removeVote($this->player);
        }
        foreach(KitManager::getKits() as $kit) {
            $kit->removeVote($this->player);
        }

        $playername = $this->player->getName();
        $items = $this->items;
        AsyncExecutor::submitMySQLAsyncTask("BuildFFA", function(mysqli $mysqli) use ($playername, $items): void {
            foreach($items as $kit => $kitItems) {
                $encodedItems = json_encode($kitItems);

                if($mysqli->query("SELECT id FROM inventory_sort WHERE playername='$playername' AND kit='$kit'")->num_rows > 0) {
                    $mysqli->query("UPDATE inventory_sort SET inventory='$encodedItems' WHERE playername='$playername' AND kit='$kit'");
                } else {
                    $mysqli->query("INSERT INTO inventory_sort(playername, inventory, kit) VALUES ('$playername', '$encodedItems', '$kit')");
                }
            }
        });
    }

    public function getPlayer(): Player{
        return $this->player;
    }

    public function getRyZerPlayer(): ?RyZerPlayer {
        return RyZerPlayerProvider::getRyzerPlayer($this->player);
    }

    public function getLastTypePlayer(string $key, int $time): ?Player {
        $cache = $this->getCache();
        $lastTick = $cache->get($key."_tick", 0);
        $last = $cache->get($key);
        if(
            ($lastTick + $time < Server::getInstance()->getTick()) ||
            $last === null
        ) return null;
        $entity = Server::getInstance()->findEntity($last);
        return ($entity instanceof Player ? $entity : null);
    }

    public function setLastTypePlayer(string $key, ?Player $last): void {
        $cache = $this->getCache();
        $cache->set($key."_tick", Server::getInstance()->getTick());
        $cache->set($key, $last?->getId());
    }

    public function hasLastTypePlayerChanged(string $key, int $time): bool {
        $cache = $this->getCache();
        $last = $cache->get($key."_last_value", -1);
        $current = $this->getLastTypePlayer($key, $time)?->getId();

        $cache->set($key."_last_value", $current);
        if($last === -1) {
            return false;
        }
        return $current !== $last;
    }

    public function isInSafeZone(): bool{
        return $this->inSafeZone;
    }

    public function setInSafeZone(bool $inSafeZone): void{
        $this->inSafeZone = $inSafeZone;
    }

    public function getItemSlot(string $kit, string $item): ?int {
        return $this->items[$kit][$item] ?? null;
    }

    public function updateItemSlot(string $kit, string $item, int $slot): void {
        $this->items[$kit][$item] = $slot;
    }

    public function giveKit(?Kit $kit = null, bool $addItemTag = false): void {
        $kit = $kit ?? GameManager::getKit();
        if($kit === null) return;
        $kitName = $kit->getName();
        $inventory = $this->player->getInventory();
        $armorInventory = $this->player->getArmorInventory();

        $inventory->clearAll();
        $armorInventory->clearAll();
        $this->player->getOffHandInventory()->clearAll();

        $invalidItems = [];
        foreach($kit->getItems() as $itemIdentifier => $item) {
            $this->player->resetItemCooldown($item, 1);
            if($item instanceof Armor) {
                $armorInventory->setItem($item->getArmorSlot(), $item);
                continue;
            }
            if($addItemTag) {
                $item = ItemUtils::addItemTag($item, $itemIdentifier, Kit::TAG_IDENTIFIER);
            }
            $slot = $this->getItemSlot($kitName, $itemIdentifier);
            if($slot === null) {
                $invalidItems[] = $item;
                continue;
            }
            $inventory->setItem($slot, $item);
        }
        foreach($invalidItems as $item) {
            $inventory->addItem($item);
        }
    }

    public function giveSafeZoneItems(): void {
        $player = $this->player;
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();
        $player->setHealth($player->getMaxHealth());
        foreach(SafeZoneManager::getItems() as $item) {
            $item->giveToPlayer($player);
        }
    }

	/**
	 * Function getPerks
	 * @return CustomItem[]
	 */
	public function getPerks(): array{
		return $this->givePerks;
	}

	/**
	 * Function givePerk
	 * @param Perk $perk
	 * @return void
	 */
	public function givePerk(Perk $perk){
		$this->givePerks[] = $perk->getItem();
	}

    public function enterSafeZone(): void {
        if($this->isInSafeZone()) return;
        $this->setInSafeZone(true);
        $player = $this->getPlayer();

        $player->removeAllEffects();
        $this->giveSafeZoneItems();
        $this->updateScoreboard();
    }

    public function leaveSafeZone(): void {
        if(!$this->isInSafeZone()) return;
        $this->setInSafeZone(false);
        $player = $this->getPlayer();

        $player->setHealth($player->getMaxHealth());
        $player->removeAllEffects();
        $this->giveKit();
        $this->updateScoreboard();

        $inv = $this->getPlayer()->getInventory();
        foreach ($this->getPerks() as $perk) {
        	$inv->addItem($perk->getItem());
		}

        $this->givePerks = [];
    }

    public function sortsInventory(): bool {
        return $this->sortsInventory;
    }

    public function sortInventory(Kit $kit): void {
        if($this->sortsInventory() || !$this->isInSafeZone()) return;
        $this->sortsInventory = true;
        $this->sortingKit = $kit;

        $player = $this->getPlayer();

        $player->setImmobile();
        $player->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 999999, 1, false));
        $player->sendMessage(BuildFFA::PREFIX.LanguageProvider::getMessageContainer("sneak-to-save", $player->getName()));
        $this->giveKit($kit, true);
    }

    public function finishInventorySort(): void {
        if(!$this->sortsInventory()) return;
        $this->sortsInventory = false;

        $player = $this->getPlayer();
        $inventory = $player->getInventory();

        if(count(array_filter($this->sortingKit->getItems(), function(Item $item): bool {
            return !$item instanceof Armor;
        })) === count($inventory->getContents())) {
            $kit = $this->sortingKit->getName();
            unset($this->items[$kit]);

            foreach($inventory->getContents() as $slot => $item) {
                if(!ItemUtils::hasItemTag($item, Kit::TAG_IDENTIFIER)) {
                    continue;
                }
                $itemIdentifier = ItemUtils::getItemTag($item, Kit::TAG_IDENTIFIER);
                $this->updateItemSlot($kit, $itemIdentifier, $slot);
            }
            $player->playSound("random.levelup", 5.0, 1.0, [$player]);
        } else {
            $player->sendMessage(BuildFFA::PREFIX."§cSomething went wrong...");
            $player->playSound("note.bass", 5.0, 1.0, [$player]);
        }

        $player->removeAllEffects();
        $player->setImmobile(false);
        $this->giveSafeZoneItems();
    }

    public function addKill(): void {
        $this->kills++;
        $this->setKillStreak($this->killStreak + 1);
        $this->updateScoreboard();
    }

    public function setKillStreak(int $streak): void {
        $this->killStreak = $streak;
        $this->player->setXpLevel($this->killStreak);
        if($this->killStreak % 3) {
        	$this->player->getRyZerPlayer()->addCoins(100, false, true);
		}
    }

    public function onDeath(): void {
        $player = $this->getPlayer();
        $killer = $this->getLastTypePlayer(self::KEY_LAST_DAMAGER, self::TIME_LAST_DAMAGER);

        $player->teleport(GameManager::getMap()->getSpawnLocation());
        $this->enterSafeZone();

        if($killer instanceof Player) {
            $bFFAKiller = BuildFFAPlayerManager::get($killer);
            $this->setLastTypePlayer(self::KEY_LAST_KILLER, $killer);
            $bFFAKiller?->setLastTypePlayer(self::KEY_LAST_KILL, $player);
            $bFFAKiller?->addKill();

            $killer->playSound("random.levelup", 5.0, 1.0, [$killer]);
            $player->playSound("note.bass", 5.0, 1.0, [$player]);
            $this->deaths++;
        }
        $this->setKillStreak(0);
        $this->setLastTypePlayer(self::KEY_LAST_DAMAGER, null);
        $this->updateScoreboard();

        $id = $player->getId();
        foreach($player->getLevel()->getEntities() as $entity) {
            if($entity->getOwningEntityId() === $id) {
                $entity->flagForDespawn();
            }
        }
    }

    public function needsScoreboardUpdate(): bool {
        return (
            $this->hasLastTypePlayerChanged(BuildFFAPlayer::KEY_LAST_KILL, self::TIME_LAST_KILL) ||
            $this->hasLastTypePlayerChanged(BuildFFAPlayer::KEY_LAST_KILLER, self::TIME_LAST_KILLER)
        );
    }

    public function updateScoreboard(bool $reset = false): void {
        $kill = $this->getLastTypePlayer(self::KEY_LAST_KILL, self::TIME_LAST_KILL);
        $killer = $this->getLastTypePlayer(self::KEY_LAST_KILLER, self::TIME_LAST_KILLER);

        $scoreboard = $this->scoreboard;
        if($reset) {
            $scoreboard->removeScoreboard();
            $scoreboard->initScoreboard();
        }
        $scoreboard->setLines([
            "",
            TextFormat::GRAY."○ Map",
            TextFormat::DARK_GRAY."⇨ ".TextFormat::GREEN.GameManager::getMap()->getName(),
            TextFormat::GRAY."○ Kit",
            TextFormat::DARK_GRAY."⇨ ".TextFormat::GREEN.GameManager::getKit()->getName(),
            "",
            TextFormat::GRAY."○ Kills",
            TextFormat::DARK_GRAY."⇨ ".TextFormat::GREEN.$this->kills.(
                $kill !== null ? TextFormat::DARK_GRAY." [".TextFormat::GREEN.$kill->getName().TextFormat::DARK_GRAY."]" : ""
            ),
            TextFormat::GRAY."○ Deaths",
            TextFormat::DARK_GRAY."⇨ ".TextFormat::GREEN.$this->deaths.(
                $killer !== null ? TextFormat::DARK_GRAY." [".TextFormat::GREEN.$killer->getName().TextFormat::DARK_GRAY."]" : ""
            ),
            TextFormat::GRAY."○ K/D",
            TextFormat::DARK_GRAY."⇨ ".TextFormat::GREEN.round(($this->kills / ($this->deaths <= 0 ? 1 : $this->deaths)), 2),
            "",
            TextFormat::AQUA."ryzer.be"
        ]);
    }
}