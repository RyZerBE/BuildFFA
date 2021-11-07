<?php


namespace BauboLP\LobbySystem\Events;


use BauboLP\Cloud\Bungee\BungeeAPI;
use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\Core\Utils\Emotes;
use BauboLP\CWBWTraining\form\SelectScenarioToSortForm;
use BauboLP\LobbySystem\Forms\BuyLottoTicketForm;
use BauboLP\LobbySystem\Forms\ReplayForm;
use BauboLP\LobbySystem\Forms\RunningClanWarForm;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\AnimationProvider;
use BauboLP\LobbySystem\Provider\LobbyGamesProvider;
use BauboLP\LobbySystem\Provider\NPCProvider;
use BauboLP\NPCSystem\entity\Geometry;
use BauboLP\NPCSystem\entity\NPC;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class DamageListener implements Listener
{

    public function onDamage(EntityDamageEvent $event)
    {
        $entity = $event->getEntity();
        if($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();

            if($damager instanceof Player) {
              if($entity instanceof NPC || $entity instanceof Geometry) {
                  $game = $entity->namedtag->getString("Game", "#CoVid19");
                  $action = $entity->namedtag->getString("Action", "#CoVid19");
                  //var_dump($action);
                  if (isset(NPCProvider::getNpc()[$game])) {
                      if (LobbySystem::getConfigProvider()->getGameSpawn($game) == null) {
                          $damager->sendTitle(TextFormat::RED . "ERROR");
                          $damager->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-no-spawn', $damager->getName(), ['#game' => $game]));
                          $damager->playSound('note.bass');
                          return;
                      }
                      $damager->addEffect(new EffectInstance(Effect::getEffect(Effect::LEVITATION), 2000, 2, false));
                      $damager->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 2000, 2, false));

                      AnimationProvider::$teleportAnimation[$damager->getName()] = ['game' => NPCProvider::getNpc()[$game]['title'], 'count' => 0, 'spawn' => LobbySystem::getConfigProvider()->getGameSpawn($game), 'title' => ""];
                  } else if ($action == "private_server") {
                      $damager->getServer()->getCommandMap()->dispatch($damager, "pserver");
                  } else if ($action == "daily_reward") {
                      $damager->getServer()->getCommandMap()->dispatch($damager, "dailyreward");
                      $entity->playEmote(Emotes::GIDDY_EMOTE);
                  }else if($action == "lotto") {
                      if(($obj = LobbySystem::getPlayerCache($damager->getName())) != null)
                      $damager->sendForm(new BuyLottoTicketForm($obj));
                      $damager->playSound('random.pling', 5, 1.0, [$damager]);
                  }else if($action == "running_cw") {
                      if(count(array_keys(LobbySystem::$runningClanWars)) != 0) {
                          $damager->sendForm(new RunningClanWarForm());
                      }else{
                          $damager->sendMessage(TextFormat::AQUA.TextFormat::BOLD."ClanWar".TextFormat::RESET." ".LanguageProvider::getMessageContainer('no-clanwar-running', $damager->getName()));
                      }
                  }else if($action == "replay") {
                      $damager->sendForm(new ReplayForm($damager->getName()));
                  }else if($action == "invsort") {
                      $game = $entity->namedtag->getString("game", "#CoVid19");
                      if($game == "#CoVid19") {
                          $damager->sendTitle(TextFormat::RED . "ERROR");
                          $damager->playSound('note.bass');
                          return;
                      }

                      if($game == "cwtraining") {
                          BungeeAPI::transfer($damager->getName(), "OnlySortCWT");
                      }
                  }else if($action == "shop"){
                      LobbySystem::getPlugin()->getServer()->dispatchCommand($damager, "shop");
                  }
              }else if($entity instanceof Player) {
                  if(LobbyGamesProvider::nearGame($entity) && LobbyGamesProvider::nearGame($damager)) {
                      $event->setBaseDamage(0);
                      return;
                  }
              }
            }else if($damager instanceof PrimedTNT && $entity instanceof Player) {
                $entity->setFlying(true);
            }
        }
        $event->setCancelled();
        if($event->getCause() === EntityDamageEvent::CAUSE_VOID && $entity instanceof Player)
            $entity->getServer()->dispatchCommand($entity, "spawn");
    }
}