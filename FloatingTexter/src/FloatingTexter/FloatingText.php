<?php
namespace FloatingTexter;
use pocketmine\entity\Entity;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\UUID;
class FloatingText extends Entity{
    /** @var string */
    public $text;
    public function __construct(Level $level, CompoundTag $nbt, string $text = ""){
        $this->setNameTag($text);
        parent::__construct($level, $nbt);
    }
    public function getText() : string{
        return $this->text;
    }
    public function setText(string $text) : void{
        $this->text = $text;
    }
    public function respawn() : void{//this is an hack. The last time I checked, the function respawnToAll() didn't work.
        $this->despawnFromAll();
        $this->spawnToAll();
    }
    public function spawnTo(Player $player){
        $pk = new AddPlayerPacket();
        $pk->uuid = UUID::fromRandom();
        $pk->username = "";
        $pk->entityRuntimeId = $this->getId();
        $pk->position = $this->asVector3();
        $pk->item = ItemFactory::get(Item::AIR, 0, 0);
        $flags = (
            (1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) |
            (1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) |
            (1 << Entity::DATA_FLAG_IMMOBILE)
        );
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
            Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, str_replace(["{name}", "{tps}", "{players}", "{maxplayers}", "{load}", "{line}"], [$player->getName(), $this->server->getTicksPerSecond(), count($this->server->getOnlinePlayers()), $this->server->getMaxPlayers(), $this->server->getTickUsage(), "\n"], $player->isOp() ? $this->getNameTag() . "\n" . TextFormat::GREEN . "ID: " . $this->getId() : $this->getNameTag())],
            Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0.01]
            ];
        $player->dataPacket($pk);
    }
}
