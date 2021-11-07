<?php


namespace BauboLP\LobbySystem\Provider;


use BauboLP\CloudSigns\Main;
use BauboLP\CloudSigns\Provider\CloudSignProvider;
use BauboLP\Core\Ryzer;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\NPCSystem\entity\Geometry;
use BauboLP\NPCSystem\entity\NPC;
use BauboLP\NPCSystem\NPCSystem;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;

class NPCProvider
{

    const PRIVATE_SERVER = 0;
    const GAME = 1;
    const DAILY_REWARD = 2;
    const LOTTO = 4;
    const CLANWAR_SPECTATE = 5;
    const SHOP = 6;
    const REPLAY = 7;
    const INV_SORT = 8;

    private static $spawned = false;
    /** @var int[] */
    public static $entitiesId = [];

//        "bedwars" => ["title" => TextFormat::RED."BedWars", 'subtitle' => TextFormat::BLACK."♠ ".TextFormat::YELLOW."FIRST VERSION ".TextFormat::BLACK."♠ ", "action" => self::GAME, "game" => "bedwars", 'pos' => "249:67:272", "skin" => "BedWars.png"],

    public static $npc = [
        "dailyreward" => ["title" => TextFormat::AQUA . "Daily Rewards", 'subtitle' => TextFormat::BLACK . "♠ " . TextFormat::YELLOW . "Pick up yet? " . TextFormat::BLACK . "♠ ", "action" => self::DAILY_REWARD, 'pos' => "-2.5:96:-3.5", "skin" => "DailyRewards.png"],
        "replay" => ["title" => TextFormat::AQUA . "Replay", 'subtitle' => TextFormat::BLACK . "♠ " . TextFormat::RED . "CLICK TO SHOW" . TextFormat::BLACK . "♠ ", "action" => self::REPLAY, 'pos' => "-8.5:96:-3.5", "skin" => "Camera.png"],
        "private_server" => ["title" => TextFormat::DARK_PURPLE . "Private Server", 'subtitle' => TextFormat::BLACK . "♠ " . TextFormat::GREEN . "NEW! " . TextFormat::BLACK . "♠ ", "action" => self::PRIVATE_SERVER, 'pos' => "-9.5:96:4.5", "skin" => "PServer.png"],
        "lotto" => ["title" => TextFormat::DARK_RED . "Lotto", 'subtitle' => TextFormat::BLACK . "♠ " . TextFormat::YELLOW . "Jackpot: 500.000 Coins " . TextFormat::BLACK . "♠ ", "action" => self::LOTTO, 'pos' => "-11.5:96:-0.5", "skin" => "Lotto.png"],
        "running_cw" => ["title" => TextFormat::RED . "Running ClanWars", 'subtitle' => TextFormat::BLACK . "♠ " . TextFormat::AQUA . "Click " . TextFormat::BLACK . "♠ ", "action" => self::CLANWAR_SPECTATE, 'pos' => "100.38:99:-11.36", "skin" => "ClanWarSpectate.png"],
        "invsort_cwtraining" => ["title" => TextFormat::DARK_AQUA . "Inventory", 'subtitle' => TextFormat::BLACK . "♠ " . TextFormat::AQUA . "Click to sort" . TextFormat::BLACK . "♠ ", "action" => self::INV_SORT, 'pos' => "40.89:88:17.76", "skin" => "PerkDealer.png"],
        "shop" => ["title" => TextFormat::GOLD . "Rank Shop", 'subtitle' => TextFormat::BLACK . "♠ " . TextFormat::AQUA . "Buy a rank" . TextFormat::BLACK . "♠ ", "action" => self::SHOP, 'pos' => "37.52:88:2.4", "skin" => "RankShop.png"],
    ];


    public static function spawnNPCS(Player $player): void
    {
        if (self::$spawned) return;


        Main::getConfigProvider()->reloadConfig();
        CloudSignProvider::loadCloudSigns();
        self::$spawned = true;
        foreach (Server::getInstance()->getDefaultLevel()->getEntities() as $entity) {
            if ($entity instanceof NPC || $entity instanceof Geometry) {
                $entity->close(); //remove all existing npc
            }
        }

        $spawn = Server::getInstance()->getDefaultLevel()->getSafeSpawn();
        foreach (self::$npc as $index => $data) {
            $i = explode(":", $data['pos']);
            $vec = new Vector3((float)$i[0], (float)$i[1], (float)$i[2]);
            if ($data['action'] == self::GAME) {
                $npc = NPCSystem::createNPC(new Position($vec->x, $vec->y, $vec->z, $player->getLevelNonNull()), NPCSystem::defaultSkin($data["skin"]), ["ALL"], $data["title"] . "\n" . $data["subtitle"], ["Action" => self::GAME]);
                $npc->setScale(1.4);
                $npc->allowLookToPlayer();
            } else if ($data['action'] == self::PRIVATE_SERVER) {
               $npc =  NPCSystem::createGeometry(new Position($vec->x, $vec->y, $vec->z, $player->getLevelNonNull()), ["pngfile" => $data["skin"], "geometryname" => "geometry.normal1", "geometrydata" => "pserver_geometry.json"], ["ALL"], $data["title"] . "\n" . $data["subtitle"], ["Action" => "private_server"]);
               $npc->setScale(1.4);
               $npc->lookAt($spawn);
            } else if ($data['action'] == self::DAILY_REWARD) {
             #   $itemEntity = Server::getInstance()->getDefaultLevel()->dropItem($vec->add(0, 2.4), Item::get(Item::DIAMOND), new Vector3(0, 0, 0));
             #   $itemEntity->onGround = true; //so it stuck in the air :)
                $npc =    NPCSystem::createNPC(new Position($vec->x, $vec->y, $vec->z, $player->getLevelNonNull()), NPCSystem::defaultSkin($data["skin"]), ["ALL"], $data["title"] . "\n" . $data["subtitle"], ["Action" => "daily_reward"]);
                $npc->setScale(1.4);
                $npc->allowLookToPlayer();
            } else if ($data['action'] == self::LOTTO) {
                $npc =    NPCSystem::createGeometry(new Position($vec->x, $vec->y, $vec->z, $player->getLevelNonNull()), ["pngfile" => $data["skin"], "geometryname" => "geometry.normal1", "geometrydata" => "lotto_geometry.json"], ["ALL"], $data["title"] . "\n" . $data["subtitle"], ["Action" => "lotto"]);
                $npc->setScale(1.4);
                $npc->lookAt($spawn);

            } else if ($data['action'] == self::CLANWAR_SPECTATE) {
                $npc =    NPCSystem::createGeometry(new Position($vec->x, $vec->y, $vec->z, $player->getLevelNonNull()), ["pngfile" => $data["skin"], "geometryname" => "geometry.idk1", "geometrydata" => "clanwar_specate.json"], ["ALL"], $data["title"] . "\n" . $data["subtitle"], ["Action" => "running_cw"]);
                $npc->setScale(1.4);
                $npc->lookAt($spawn);

            } else if ($data['action'] == self::REPLAY) {
                $npc =   NPCSystem::createGeometry(new Position($vec->x, $vec->y, $vec->z, $player->getLevelNonNull()), ["pngfile" => "Camera.png", "geometryname" => "geometry.Mobs.Zombie", "geometrydata" => "camera_geometry.json"], ["ALL"], $data["title"] . "\n" . $data["subtitle"], ["Action" => "replay"]);
                $npc->setScale(1.6);
                $npc->lookAt($spawn);

            } else if ($data['action'] == self::SHOP) {
                $npc =   NPCSystem::createGeometry(new Position($vec->x, $vec->y, $vec->z, $player->getLevelNonNull()), ["pngfile" => "RankShop.png", "geometryname" => "geometry.Mobs.Zombie", "geometrydata" => "rankshop_geometry.json"], ["ALL"], $data["title"] . "\n" . $data["subtitle"], ["Action" => "shop"]);
                $npc->setScale(1.6);
                $npc->lookAt($spawn);

            }else if($data['action'] == self::INV_SORT) {
                $npc =    NPCSystem::createNPC(new Position($vec->x, $vec->y, $vec->z, $player->getLevelNonNull()), NPCSystem::defaultSkin($data["skin"]), ["ALL"], $data["title"] . "\n" . $data["subtitle"], ["Action" => "invsort", "game" => "cwtraining"]);
                $npc->setScale(1.2);
                $npc->allowLookToPlayer();

            }
        }

        foreach (Server::getInstance()->getDefaultLevel()->getEntities() as $entity) {
            if ($entity instanceof NPC) {
                self::$entitiesId[$entity->getId()] = ['tag' => $entity->namedtag, 'type' => "human", 'nametag' => $entity->getNameTag(), 'size' => $entity->getScale()];
                //var_dump("Save human ".$entity->getId()." ...");
            } elseif ($entity instanceof Geometry) {
                self::$entitiesId[$entity->getId()] = ['tag' => $entity->namedtag, 'type' => "model", 'nametag' => $entity->getNameTag(), 'size' => $entity->getScale()];
                //var_dump("Save model ".$entity->getId()." ...");
            }
        }

        LobbySystem::getPlugin()->getScheduler()->scheduleRepeatingTask(new class extends Task {

            /**
             * @inheritDoc
             */
            public function onRun(int $currentTick)
            {
                $server = LobbySystem::getPlugin()->getServer();
                foreach (array_keys(NPCProvider::$entitiesId) as $entityId) {
                    if ($server->getDefaultLevel()->getEntity($entityId) == null) {
                        /** @var array $entityTag */
                        $entityTag = NPCProvider::$entitiesId[$entityId];
                        //  var_dump($entityTag['type']." respawned!");
                        unset(NPCProvider::$entitiesId[$entityId]);
                        if ($entityTag['type'] == "human") {
                            //   $human = new NPCHuman($server->getDefaultLevel(), $entityTag['tag']);
                            $human = new NPC($server->getDefaultLevel(), $entityTag['tag']);
                            $human->spawnToAll();
                            $human->setNameTag($entityTag['nametag']);
                            $human->setScale($entityTag["size"]);
                            //var_dump($human->getNameTag());
                            NPCProvider::$entitiesId[$human->getId()] = ['tag' => $human->namedtag, 'type' => "human", 'nametag' => $human->getNameTag(), 'size' => $human->getScale()];
                        } else {
                            //$human = new NPCModel($server->getDefaultLevel(), $entityTag['tag']);
                            $human = new Geometry($server->getDefaultLevel(), $entityTag['tag']);
                            $human->spawnToAll();
                            $human->setScale($entityTag["size"]);
                            $human->setNameTag($entityTag['nametag']);
                            // var_dump($human->getNameTag());
                            NPCProvider::$entitiesId[$human->getId()] = ['tag' => $human->namedtag, 'type' => "model", 'nametag' => $human->getNameTag(), 'size' => $human->getScale()];
                        }
                    }
                }
            }
        }, 20);
    }


    /**
     * @return array
     */
    public static function getNpc(): array
    {
        return self::$npc;
    }
}