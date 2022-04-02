<?php

namespace kitpvp;

use pocketmine\block\DiamondOre;
use pocketmine\block\Door;
use pocketmine\block\Wood;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;


use pocketmine\entity\effect\HungerEffect;
use pocketmine\entity\Entity;
use pocketmine\entity\HungerManager;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBedLeaveEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\permission;


use pocketmine\command\defaults\TeleportCommand;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\player\PlayerInfo;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{

    protected function onEnable(): void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("§e§lPlugin activataed");
        if(!file_exists($this->getDataFolder() . "KitFFA")){
            @mkdir($this->getDataFolder() . "KitFFA");
        }
        if(!file_exists($this->getDataFolder() . "KitFFA/kills")){
            @mkdir($this->getDataFolder() . "KitFFA/kills");
        }
        if(!file_exists($this->getDataFolder() . "KitFFA/Money")){
            @mkdir($this->getDataFolder() . "KitFFA/Money");
        }
        if(!file_exists($this->getDataFolder() . "KitFFA/Kits")){
            @mkdir($this->getDataFolder() . "KitFFA/Kits");
        }
        if(!file_exists($this->getDataFolder() . "KitFFA/isIngame")){
            @mkdir($this->getDataFolder() . "KitFFA/isIngame");
        }
    }


    public function EnableFight(EntityDamageEvent $e){
        $player = $e->getEntity();
        $cause = $e->getCause();
        if($player instanceof Player){
            if($cause == EntityDamageEvent::CAUSE_ENTITY_ATTACK){
                if(file_exists($this->getDataFolder() . "KitFFA/isIngame/" . $player->getName())){
                    $e->uncancel();
                    return false;
                }else{
                    $e->cancel();
                }
            }
        }
    }

    public function sendems(Player $player){
        $player->sendMessage("§4You are now in the Fight!");
        $player->setGamemode(GameMode::SURVIVAL());
    }

    public function onDamage(EntityDamageEvent $e){
        $player = $e->getEntity();
        $cause = $e->getCause();
        if($player instanceof Player){
            if($cause == EntityDamageEvent::CAUSE_FALL){
                $e->cancel();
                if(!file_exists($this->getDataFolder() . "KitFFA/isIngame/" . $player->getName())){
                    $this->sendems($e->getEntity());
                }
                @mkdir($this->getDataFolder() . "KitFFA/isIngame/" . $player->getName());
            }
        }
    }

    public function onJoin(PlayerJoinEvent $e){
        $e->getPlayer()->respawn();
        $e->getPlayer()->getInventory()->clearAll();
        @rmdir($this->getDataFolder() . "KitFFA/isIngame/" . $e->getPlayer()->getName());
        $e->getPlayer()->getInventory()->clearAll();
        $e->getPlayer()->getInventory()->setItem(0, ItemFactory::getInstance()->get(268, 0)->setCustomName("Normal Sword"));
        $e->setJoinMessage("§e" . $e->getPlayer()->getName() . " §ahas joined the game!");

        if(!file_exists($this->getDataFolder() . "KitFFA/Kits/" . $e->getPlayer()->getName())){
            @mkdir($this->getDataFolder() . "KitFFA/Kits/" . $e->getPlayer()->getName());
        }else{
            $e->setJoinMessage("§e" . $e->getPlayer()->getName() . " §ahas joined the game!");
        }
        if(!file_exists($this->getDataFolder() . "KitFFA/Money/" . $e->getPlayer()->getName())){
            @mkdir($this->getDataFolder() . "KitFFA/Money/" . $e->getPlayer()->getName());
        }else{
            $e->setJoinMessage("§e" . $e->getPlayer()->getName() . " §ahas joined the game!");
        }
        if(!file_exists($this->getDataFolder() . "KitFFA/Money/" . $e->getPlayer()->getName())){
            @mkdir($this->getDataFolder() . "KitFFA/Money/" . $e->getPlayer()->getName());
        }else{
            $e->setJoinMessage("§e" . $e->getPlayer()->getName() . " §ahas joined the game!");
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{

        /////////////////////////////////////////////////////

        //____________________KIT_____________________//

        switch ($command->getName()){
            case "kit":
                if($sender instanceof Player){
                    if(file_exists($this->getDataFolder() . "KitFFA/isIngame/" . $sender->getName())){
                        $sender->sendMessage("§eYou can't choose Kits in the fighting zone");
                        return false;
                    }
                    $this->kitUI($sender);
                    new Config($this->getDataFolder() . "KitFFA/Money/" . $sender->getName() . "/money.yml", Config::YAML);
                }
        }
        //____________________KIT_____________________//

        /////////////////////////////////////////////////////

        //____________________SETCOINS_____________________//

        switch ($command->getName()){
            case "setcoins":
                if($sender instanceof Player){
                    if(isset($args[0])){
                        $player = $this->getServer()->getPlayerByPrefix($args[0]);
                        if(!$player->isOnline()){
                            $sender->sendMessage("§4Player not found");
                            return false;
                        }
                        if(!$player === null): bool{
                            $sender->sendMessage("Usage: /setcoins >player< <--> >amount<");
                        }else{
                            $money = new Config($this->getDataFolder() . "KitFFA/Money/" . $player->getName() . "/money.yml", Config::YAML);

                                if($args[1] === null){
                                    $sender->sendMessage("Usage: /setcoins >player< <--> >amount<");
                                    return false;
                                }
                                if(is_numeric($args[1])){
                                    $money->set("money", $args[1]);
                                    $money->save();
                                     $money->reload();
                                     $sender->sendMessage("§aYou gave to §4" . $player->getName() . "§a " . $args[1] . "§e Coins!");
                                     $player->sendMessage("§aYou got from" . $sender->getName() . " §a" . $args[1] . "§e Coins");
                                }else{
                                    $sender->sendMessage("Usage: /setcoins >player< <--> >amount<");
                              }
                        }
                    }else{
                        $sender->sendMessage("Usage: /setcoins >player< <--> >amount<");
                    }
                }else{
                    $sender->sendMessage("Ingame!");
                }
        }

        //____________________SETCOINS_____________________//

        /////////////////////////////////////////////////////

        //____________________COINS_____________________//

        switch ($command->getName()){
            case "coins":
                if($sender instanceof Player){
                    if(!file_exists($this->getDataFolder() . "KitFFA/Money/" . $sender->getName() . "/money.yml")){
                        $sender->sendMessage("§eYou dont have any Coins");
                    }else{
                        $conf = new Config($this->getDataFolder() . "KitFFA/Money/" . $sender->getName() . "/money.yml", Config::YAML);
                        $sender->sendMessage("§aYou have §e" . $conf->get("money") . " §aCoins");
                    }
                }
        }
        //____________________COINS_____________________//

        /////////////////////////////////////////////////////

        return true;
    }
    public function kitUI($sender){
        $form = new SimpleForm(function (Player $sender, int $data = null){
            if($data === null){
                return;
            }
            $conf = new Config($this->getDataFolder() . "KitFFA/Money/" . $sender->getName() . "/money.yml", Config::YAML);
            if(!$conf->exists("money")){
                $conf->set("money", "0");
                $conf->save();
                $conf->reload();
            }
            switch ($data){


                //_____________________________KIT1___________________________//


                case 0:
                    if(file_exists($this->getDataFolder() . "KitFFA/Kits/" . $sender->getName() . "/kit1.yml")) {
                        $sender->sendMessage("§eYou selected the §4Basic Kit!");
                        $sender->getInventory()->clearAll();
                        $sender->getInventory()->setItem(0, ItemFactory::getInstance()->get(268, 0)->setCustomName("Basic Sword"));
                        $sender->getInventory()->setItem(1, ItemFactory::getInstance()->get(298, 0)->setCustomName("Basic Helmet"));
                        break;
                    }else{
                        if($conf->get("money") >= "20"){
                            $moneynow = $conf->get("money");
                            $conf->set("money", $moneynow -= 20);
                            $conf->save();
                            $conf->reload();
                            new Config($this->getDataFolder() . "KitFFA/Kits/" . $sender->getName() . "/kit1.yml", Config::YAML);
                            $sender->sendMessage("§eYou buyd the §4Basic Kit!");
                            break;
                        }else{
                            $sender->sendMessage("§cYou don't have enough §eCoins!");
                            break;
                        }
                    }
                case 1:
                    if(file_exists($this->getDataFolder() . "KitFFA/Kits/" . $sender->getName() . "/kit2.yml")) {
                        $sender->sendMessage("§eYou selected the §4Woodtrooper Kit!");
                        $sender->getInventory()->clearAll();
                        $sender->getInventory()->setItem(0, ItemFactory::getInstance()->get(275, 0)->setCustomName("Woodtrooper Axe"));
                        $sender->getInventory()->setItem(1, ItemFactory::getInstance()->get(299, 0)->setCustomName("Woodtrooper Chestplate"));
                        break;
                    }else{
                        if($conf->get("money") >= "40"){
                            $moneynow = $conf->get("money");
                            $conf->set("money", $moneynow -= 40);
                            $conf->save();
                            $conf->reload();
                            new Config($this->getDataFolder() . "KitFFA/Kits/" . $sender->getName() . "/kit2.yml", Config::YAML);
                            $sender->sendMessage("§eYou buyd the §4Woodtrooper Kit");
                            break;
                        }else{
                            $sender->sendMessage("§cYou don't have enough §eCoins!");
                            break;
                        }
                    }

                //_____________________________KIT1___________________________//



            }

        });
        $form->setTitle("§rKitFFA");
        $form->setContent("Buy or Select some Kits");
        $form->addButton("§4BasicKit\n§a20 §eCoins");
        $form->addButton("§4WoodTrooperKit\n§a40 §eCoins");
        $sender->sendForm($form);
    }



    //______________________DEATHEVENT_________________________//

    public function onPlayerDeathEvent(PlayerDeathEvent $event)
    {
        $player = $event->getEntity();
        if(file_exists($this->getDataFolder() . "KitFFA/isIngame/" . $player->getName())){
            @rmdir($this->getDataFolder() . "KitFFA/isIngame/" . $player->getName());
        }
        if ($player instanceof Player)
        {
            $cause = $player->getLastDamageCause();

            if($cause instanceof EntityDamageByEntityEvent)
            {
                $damager = $cause->getDamager();
                if($damager instanceof Player)
                {
                    $moneyplayer = new Config($this->getDataFolder() . "KitFFA/Money/" . $player->getName() . "/money.yml", Config::YAML);
                    $moneydamager = new Config($this->getDataFolder() . "KitFFA/Money/" . $damager->getName() . "/money.yml", Config::YAML);

                    $moneynowplayer = $moneyplayer->get("money");
                    $moneyplayer->set("money", $moneynowplayer -= 5);
                    $moneyplayer->save();
                    $moneyplayer->reload();

                    $moneynowdamager = $moneydamager->get("money");
                    $moneydamager->set("money", $moneynowdamager += 5);
                    $moneydamager->save();
                    $moneydamager->reload();

                    $name = $damager->getName();
                    $event->setDeathMessage("§e" . $player->getName() . " §awas slay by §4" . $damager->getName());

                    $damager->setHealth(20);
                    $damager->sendPopup("§a+ §e5 Coins");

                    if(!file_exists($this->getDataFolder() . "KitFFA/kills/" . $damager->getNameTag())){
                        @mkdir($this->getDataFolder() . "KitFFA/kills/" . $damager->getNameTag());
                        $kills = new Config($this->getDataFolder() . "KitFFA/kills/" . $damager->getNameTag() . "kills.yml", Config::YAML);
                        $kills->set("kills", 0);
                    }else{
                        $kills = new Config($this->getDataFolder() . "KitFFA/kills/" . $damager->getNameTag() . "kills.yml", Config::YAML);
                        $kills->set("kills", $kills->get("kills") + 1);
                        $kills->save();
                        $kills->reload();
                    }

                }
            }
        }
    }
    public function onRe(PlayerRespawnEvent $e){
        $e->getPlayer()->getInventory()->setItem(0, ItemFactory::getInstance()->get(268, 0)->setCustomName("Normal Sword"));
    }
    public function onDr(PlayerDropItemEvent $e){
        $e->cancel();
    }
    public function onbui(BlockBreakEvent $e){
        if($this->getServer()->isOp($e->getPlayer()->getName())){
            $e->uncancel();
        }else{
            $e->cancel();
        }
    }
    public function build(BlockPlaceEvent $e){
        if($this->getServer()->isOp($e->getPlayer()->getName())){
            $e->uncancel();
        }else{
            $e->cancel();
        }
    }
    public function leave(PlayerQuitEvent $e){
        if(file_exists($this->getDataFolder() . "KitFFA/isIngame/" . $e->getPlayer()->getName())){
            $e->getPlayer()->kill();
            @rmdir($this->getDataFolder() . "KitFFA/isIngame/" . $e->getPlayer()->getName());
        }
        $e->setQuitMessage("§e" . $e->getPlayer()->getName() . "§a left the Game.");
    }

    //______________________DEATHEVENT_________________________//

    //______________________NoHUNGER_______________________//

        public function nohunger(Player $player){
            $player->getHungerManager()->setEnabled(false);
        }

    //______________________NoHUNGER_______________________//


}
