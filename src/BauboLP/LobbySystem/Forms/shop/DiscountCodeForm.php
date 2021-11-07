<?php


namespace BauboLP\LobbySystem\Forms\shop;


use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\CreatorCodeProvider;
use pocketmine\form\CustomForm;
use pocketmine\form\CustomFormResponse;
use pocketmine\form\element\Input;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class DiscountCodeForm extends CustomForm
{

    public function __construct(string $rank, array $rankData, string $playerName)
    {
        $elements = [new Input("Discount Code", LanguageProvider::getMessageContainer('lobby-give-discount-code', $playerName), "????", TextFormat::RED."No Code")];
        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA."Discount Code", $elements, function (Player $player, CustomFormResponse $response) use ($rankData, $rank): void{
                $e = $this->getElement(0);
                $code = $response->getString($e->getName());
                if(TextFormat::clean($code) == "No Code") {
                    $player->sendForm(new BuyForm(null, $rankData['cost'], $rank, $player->getName()));
                    return;
                }
                if(!is_string($code) || !CreatorCodeProvider::existCreatorCode($code)) {
                    $player->sendMessage(LobbySystem::Prefix.LanguageProvider::getMessageContainer('lobby-wrong-discount-code', $player->getName()));
                    return;
                }

                $percent = CreatorCodeProvider::getCreatorCodeData($code)['percent'];
                $rechnung = $percent * $rankData['cost'];
                $discount = $rechnung / 100;
                $newPrice = $rankData['cost'] - $discount;
                $player->sendForm(new BuyForm($code, $newPrice, $rank, $player->getName()));
        });
    }
}