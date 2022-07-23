<?php

use pocketmine\network\mcpe\protocol\{BatchPacket, DataPacket, PacketPool};
use raklib\protocol\
{
    Datagram,
    EncapsulatedPacket
};
use raklib\Session;
require_once("./basic/Writer.php");

class Packet{
    
    private static $number = 0;
    private static $splitPackets = [];

    public static $storage;

    public static $cancelQueue = [];

    public static function init(){
		self::$storage = new \SplObjectStorage;
	}

    public static function readDataPacket(string $buffer) : ?DataPacket{
		$pid = ord($buffer[0]);
		if(($pid & Datagram::BITFLAG_VALID) !== 0){
			if($pid & Datagram::BITFLAG_ACK){
				//TODO
			}elseif($pid & Datagram::BITFLAG_NAK){
				//TODO
			}else{
				if(($datagram = new Datagram($buffer)) instanceof Datagram){
					$datagram->decode();
					self::$number = $datagram->seqNumber;
					foreach($datagram->packets as $packet){
						if($packet->hasSplit){
							$split = self::decodeSplit($packet);
							if($split !== null){
								$packet = $split;
							}
						}
						if(($pk = self::decodeBatch($packet)) !== null){
							self::$storage[$pk] = $datagram->seqNumber;
							return $pk;
						}
					}
				}
			}
		}
		return null;
	}

	public static function decodeSplit(EncapsulatedPacket $packet) : ?EncapsulatedPacket{
		if($packet->splitCount >= Session::MAX_SPLIT_SIZE or $packet->splitIndex >= Session::MAX_SPLIT_SIZE or $packet->splitIndex < 0){
			return null;
		}

		if(!isset(self::$splitPackets[$packet->splitID])){
			if(count(self::$splitPackets) >= Session::MAX_SPLIT_COUNT){
				return null;
			}
			self::$splitPackets[$packet->splitID] = [$packet->splitIndex => $packet];
		}else{
			self::$splitPackets[$packet->splitID][$packet->splitIndex] = $packet;
		}

		if(count(self::$splitPackets[$packet->splitID]) === $packet->splitCount){
			$pk = new EncapsulatedPacket;
			$pk->buffer = "";
			for($i = 0; $i < $packet->splitCount; ++$i){
				$pk->buffer .= self::$splitPackets[$packet->splitID][$i]->buffer;
			}

			$pk->length = strlen($pk->buffer);
			unset(self::$splitPackets[$packet->splitID]);
			return $pk;
		}
		return null;
	}

	public static function decodeBatch(EncapsulatedPacket $encapsulatedPacket) : ?DataPacket{
		if(($batch = PacketPool::getPacket($encapsulatedPacket->buffer)) instanceof BatchPacket){
			@$batch->decode();
			if($batch->payload !== "" && is_string($batch->payload)){
				foreach($batch->getPackets() as $buf){
					return PacketPool::getPacket($buf);
				}
			}
		}
		return null;
	}

	public static function writeDataPacket(DataPacket $packet, Writer $writer) : void{
		$batch = new BatchPacket;
		$batch->addPacket($packet);
		$batch->setCompressionLevel(7);
		$batch->encode();
		$encapsulated = new EncapsulatedPacket;
		$encapsulated->reliability = 0;
		$encapsulated->buffer = $batch->buffer;
		$dataPacket = new Datagram;
		$dataPacket->seqNumber = self::$number++;
		$dataPacket->packets = [$encapsulated];
		$dataPacket->encode();
		$writer->writePacket($dataPacket->buffer);
	}
}