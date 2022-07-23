<?php

use pocketmine\network\mcpe\protocol\DataPacket;
require_once("./Lib.php");
require_once("./other/Address.php");
require_once("./other/Packet.php");

abstract class Writer{
    
    protected $proxy;

    public $address;

    public function __construct(Lib $lib, ?Address $address){
        $this->lib = $lib;
        $this->address = $address;
    }

    public function getLib() : Lib{
        return $this->lib;
    }

    public function writePacket(string $buffer) : void{
        $this->getLib()->writePacket($buffer, $this->address->ip, $this->address->port);
    }

    public function dataPacket(DataPacket $packet) : void{
        Packet::writeDataPacket($packet, $this);
    }

    public function handleDataPacket(DataPacket $packet) : bool{}
}