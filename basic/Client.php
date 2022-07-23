<?php

use pocketmine\network\mcpe\protocol\
{
    DataPacket,
    TextPacket
};
require_once("./Lib.php");
require_once("./other/Address.php");

class Client extends Writer{
    public $connected = false;
    
    public function __construct(Proxy $proxy, ?Address $address){
		parent::__construct($proxy, $address);
	}

    public function sendMessage(string $message, int $type = 0) : void{
        $pk = new TextPacket();
        $pk->type = $type;
        $pk->message = $message;
        $this->dataPacket($pk); //Sending packet to a client
    }

    public function handleDataPacket(DataPacket $packet) : bool{
        if($packet instanceof TextPacket){
            $packet->decode();
            if($packet->message === ".test"){
                $this->sendMessage("test message");
                return false; //Cancelling packet
            }
        }
    }
}