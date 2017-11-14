<?php

namespace FloatingTexter;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase{

    public function onEnable(){
        $this->saveDefaultConfig();
        Entity::registerEntity(FloatingText::class, true);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new RefreshTask($this), $this->getConfig()->get("time") * 20);
        $this->getLogger()->info(TextFormat::GREEN . "FloatingTexter by Fycarman enabled!");
    }

    public function onDisable(){
        $this->getLogger()->info(TextFormat::RED . "FloatingTexter by Fycarman disabled.");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        if($sender instanceof Player){
            if($sender->hasPermission("floatingtexter.use")){

		/* config Y pos
         * @param ypos in config.yml
         * Genboy 2017 11
         */
		$ftypos = 1; // default y pos
		if( $this->getConfig()->get("ypos") > 1 ){
			$ftypos = $this->getConfig()->get("ypos");
		}

                if(isset($args[0])){
                    switch(array_shift($args)){
                        case "add":
                            $this->getServer()->getScheduler()->scheduleDelayedTask(new SpawnTask($this, Entity::createEntity("FloatingText", $sender->getLevel(), new CompoundTag("", [
                                "Pos" => new ListTag("Pos", [
                                    new DoubleTag("", $sender->getX()),
                                    new DoubleTag("", $sender->getY() + $ftypos), // config Y pos
                                    new DoubleTag("", $sender->getZ())
                                ]),
                                "Motion" => new ListTag("Motion", [
                                    new DoubleTag("", 0),
                                    new DoubleTag("", 0),
                                    new DoubleTag("", 0)
                                ]),
                                "Rotation" => new ListTag("Rotation", [
                                    new FloatTag("", lcg_value() * 360),
                                    new FloatTag("", 0)
                                ]),
                            ]), implode(" ", $args))), 20);
                            $sender->sendMessage(TextFormat::GREEN . "FloatingText spawned at your position!");
                            break;
                        case "remove":
                            if(isset($args[0]) and is_numeric($args[0])){
                                $floatingText = $sender->getLevel()->getEntity($args[0]);
                                if($floatingText instanceof FloatingText){
                                    $floatingText->kill();
                                    $sender->sendMessage(TextFormat::RED . "FloatingText successfully removed.");
                                }else{
                                    $sender->sendMessage(TextFormat::RED . "The specified entity id is not a FloatingText!");
                                }
                            }else{
                                $sender->sendMessage(TextFormat::YELLOW . "Usage: /floatingtexter remove <id>");
                            }
                            break;
                        case "edit":
                            if(isset($args[0]) and is_numeric($args[0])){
                                $text = implode(" ", $args);
                                $floatingText = $sender->getLevel()->getEntity($args[0]);
                                if($floatingText instanceof FloatingText){
                                    if(trim($text, " ") !== ""){
                                        $floatingText->setText($text);
                                        $floatingText->respawn();
                                        $sender->sendMessage(TextFormat::RED . "FloatingText successfully updated!");
                                    }else{
                                        $sender->sendMessage(TextFormat::RED . "Please input a valid text.");
                                    }
                                }else{
                                    $sender->sendMessage(TextFormat::RED . "The specified entity id is not a FloatingText!");
                                }
                            }else{
                                $sender->sendMessage(TextFormat::YELLOW . "Usage: /floatingtexter edit <id> <text>");
                            }
                            break;
                        default:
                            $sender->sendMessage(TextFormat::YELLOW . "Usage: /floatingtexter <add|remove|edit> <text|id>");
                    }
                }
            }
        }
        return true;
    }
}
