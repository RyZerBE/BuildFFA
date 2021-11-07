<?php


namespace BauboLP\LobbySystem\Tasks;


use BauboLP\Cloud\CloudBridge;
use BauboLP\CloudSigns\Main;
use BauboLP\CloudSigns\Provider\CloudSignProvider;
use BauboLP\CloudSigns\Utils\CloudSign;
use BauboLP\LobbySystem\LobbySystem;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;

class LobbyCountTask extends Task
{

    public function onRun(int $currentTick)
    {
        Server::getInstance()->getAsyncPool()->submitTask(new class extends AsyncTask {


            private $cloudBridge;

            public function __construct()
            {
                $this->cloudBridge = CloudBridge::getCloudProvider();
            }
            /**
             * @inheritDoc
             */
            public function onRun()
            {
                $data = [];
                foreach ($this->cloudBridge->getRunningServersByGroup("Lobby") as $lobby) {
                    if (file_exists("/root/RyzerCloud/servers/$lobby/server.properties")) {
                        $c = new Config("/root/RyzerCloud/servers/$lobby/server.properties");
                        $port = $c->get("server-port");
                        $info = CloudSignProvider::getQueryInfo(CloudSign::IP, (int)$port);
                        $data[$lobby] = $info['online'];
                    }
                }
                $this->setResult($data);
            }

            public function onCompletion(Server $server)
            {
                $data = $this->getResult();
                LobbySystem::$lobbys = $data;
            }
        });
    }
}