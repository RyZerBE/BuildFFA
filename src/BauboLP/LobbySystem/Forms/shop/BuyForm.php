<?php


namespace BauboLP\LobbySystem\Forms\shop;


use BauboLP\Core\Player\RyzerPlayerProvider;
use BauboLP\Core\Provider\CoinProvider;
use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\Core\Provider\RankProvider;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\CreatorCodeProvider;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class BuyForm extends MenuForm
{

    public function __construct(?string $code, int $cost, string $rank, string $playerName)
    {
        $options = [];
        $options[] = new MenuOption(TextFormat::GREEN."Buy");
        $options[] = new MenuOption(TextFormat::RED."Cancel");

        if($code == null) {
            $text = LanguageProvider::getMessageContainer('lobby-buy-price-info', $playerName, ['#cost' => $cost]);
        }else {
            $text = LanguageProvider::getMessageContainer('lobby-buy-price-info-with-code', $playerName, ['#code' => $code, '#percent' => CreatorCodeProvider::getCreatorCodeData($code)['percent']."%", '#cost' => $cost]);
        }
        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA."Buy?", $text, $options, function (Player $player, int $selectedOption) use($code, $cost, $rank): void{

            if(($obj = RyzerPlayerProvider::getRyzerPlayer($player->getName())) != null) {
                if ($selectedOption == 0) {
                    if($obj->getCoins() < $cost) {
                        $player->sendMessage(LobbySystem::Prefix.LanguageProvider::getMessageContainer('not-enough-coins', $player->getName()));
                        return;
                    }

                    if(RankProvider::getRankJoinPower($obj->getRank()) >= RankProvider::getRankJoinPower($rank)) {
                        $player->sendMessage(LobbySystem::Prefix.LanguageProvider::getMessageContainer('rank-higher-rankshop', $player->getName()));
                        return;
                    }

                    CoinProvider::removeCoins($player->getName(), $cost);
                    Server::getInstance()->getCommandMap()->dispatch(new ConsoleCommandSender(), "rperm setrank {$player->getName()} $rank");
                    $player->sendForm(new SuccessfulBuyInfoForm($player->getName()));
                }
            }
        });
    }
}