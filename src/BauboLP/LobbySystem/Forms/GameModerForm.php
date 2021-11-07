<?php


namespace BauboLP\LobbySystem\Forms;


use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\AnimationProvider;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\form\FormIcon;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class GameModerForm extends MenuForm
{

    private $images = [
      "bedwars" => "https://media.discordapp.net/attachments/700365538999926804/810212283984117780/BWPOG.png?width=702&height=702",
      "mlgrush" => "https://media.discordapp.net/attachments/700365538999926804/810874920820670505/Mlgrush.png?width=702&height=702",
      "clutches" => "https://media.discordapp.net/attachments/700365538999926804/810874937421594704/clutches.png?width=702&height=702",
      "ffa" => "https://media.discordapp.net/attachments/700365538999926804/810874999648026644/FFA.png?width=702&height=702",
      "cw training" => "https://media.discordapp.net/attachments/700365538999926804/810875379539509248/CWT.png?width=702&height=702",
      "cwtraining" => "https://media.discordapp.net/attachments/700365538999926804/810875379539509248/CWT.png?width=702&height=702",
      "cw-training" => "https://media.discordapp.net/attachments/700365538999926804/810875379539509248/CWT.png?width=702&height=702",
      "clanwar" => "https://media.discordapp.net/attachments/700365538999926804/810875393238237194/CW.png?width=702&height=702",
    ];

    public function __construct()
    {
        $options = [];
        $spawns = [];
        $games = [TextFormat::GOLD."FlagWars", TextFormat::RED.'BedWars', TextFormat::AQUA.'ClanWar', TextFormat::YELLOW.'CW-Training'
            , TextFormat::AQUA.'M'.TextFormat::WHITE."L".TextFormat::AQUA."GRush", TextFormat::GOLD.'FFA',
            TextFormat::RED."Clutches", TextFormat::GRAY."Spawn"];
        foreach ($games as $game) {
            $index = strtolower(TextFormat::clean($game));
            $img = null;
            if (array_key_exists($index, $this->images))
                $img = new FormIcon($this->images[$index]);

            $options[] = new MenuOption(str_replace("-", " ", $game), $img);
            $spawns[] = LobbySystem::getConfigProvider()->getGameSpawn($index);
        }
        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA."Games", "", $options, function (Player $player, int $selectedOption) use($spawns, $games): void{
            $spawn = $spawns[$selectedOption];
            $game = $games[$selectedOption];

            if($spawn == null) {
                $player->sendMessage(LobbySystem::Prefix.LanguageProvider::getMessageContainer('lobby-no-spawn', $player->getName(), ['#game' => $game]));
                return;
            }

            $player->addEffect(new EffectInstance(Effect::getEffect(Effect::LEVITATION), 2000, 2, false));
            $player->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 2000, 2, false));

            AnimationProvider::$teleportAnimation[$player->getName()] = ['game' => $game, 'count' => 0, 'spawn' => $spawn, 'title' => ""];
        }, null);
    }
}