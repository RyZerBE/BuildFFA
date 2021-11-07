<?php


namespace BauboLP\LobbySystem\Forms;


use BauboLP\Core\Player\RyzerPlayerProvider;
use BauboLP\Core\Provider\CoinProvider;
use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Utils\LobbyPlayer;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class BuyLottoTicketForm extends MenuForm
{

    public function __construct(LobbyPlayer $player)
    {
        $options = [];
        $button = new MenuOption(TextFormat::GOLD."1x LottoTicket\n".TextFormat::YELLOW."Click to play");
        $options[] = new MenuOption(TextFormat::RED."Buy a LottoTicket\n".TextFormat::YELLOW."1000 Coins per ticket");
        for($i = 0; $i < $player->getTickets(); $i++)
            $options[] = $button;
        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA."Lotto", LanguageProvider::getMessageContainer('lobby-lotto-dangerous', $player->getPlayer()->getName()), $options, function (Player $player, int $selectedOption): void {
            if ($selectedOption == 0) {
                if (($obj = RyzerPlayerProvider::getRyzerPlayer($player->getName())) != null) {
                    if ($obj->getCoins() < 1000) {
                        $player->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('not-enough-coins', $player->getName()));
                        return;
                    }

                    CoinProvider::removeCoins($player->getName(), 1000);
                    if (($lobbyPlayer = LobbySystem::getPlayerCache($player->getName())) != null) {
                        LobbySystem::getLottoProvider()->addTicket($lobbyPlayer);
                        $player->playSound('random.levelup', 5, 1.0, [$player]);
                        $player->sendForm(new BuyLottoTicketForm($lobbyPlayer));
                    }
                }
            } else {
                if (($lobbyPlayer = LobbySystem::getPlayerCache($player->getName())) != null)
                LobbySystem::getLottoProvider()->removeTicket($lobbyPlayer);
                LobbySystem::getLottoProvider()->sendLottoMenu($player);
            }
        });
    }
}