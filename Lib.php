<?php

use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\utils\Terminal;
use raklib\protocol\
{
    OpenConnectionRequest1,
    ACK,
    UnconnectedPing,
    UnconnectedPong
};
require_once("./other/Address.php");
require_once("./other/Packet.php");
require_once("./other/Logger.php");
require_once("./basic/Client.php");
require_once("./basic/Server.php");
require_once("./basic/Writer.php");

class Lib{
    private $socket;

    private $server;

    private $client;

    public function __construct(string $serverAddress, int $serverPort, string $interface, int $bindPort){
        Terminal::init();
        PacketPool::init();
        Packet::init();

        $this->server = new Server($this, new Address($serverAddress, $serverPort));
        $this->client = new Client($this, null);

        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        
        socket_set_option($this->socket, SOL_SOCKET, SO_SNDBUF, 1024 * 1024 * 8);
		socket_set_option($this->socket, SOL_SOCKET, SO_RCVBUF, 1024 * 1024 * 8);

        while(true){
			if(@socket_recvfrom($this->socket, $buffer, 65535, 0, $address, $port) !== false){
				$internetAddress = new Address($address, $port);
				if(!$this->client->connected){
					switch(ord($buffer[0])){
						case UnconnectedPing::$ID:
							$this->client->address = $internetAddress;
							$this->sendToServer($buffer);
							break;
						case UnconnectedPong::$ID:
							$this->server->data = explode(";", substr($buffer, 40));
							$this->sendToClient($buffer);
							break;
						case OpenConnectionRequest1::$ID:
							$this->client->address = $internetAddress;
							$this->client->connected = true;
							$this->sendToServer($buffer);
							break;
					}
				}else{
					if($this->server->address->equals($internetAddress)){
						if($this->decodeBuffer($buffer, $this->server)){
							$this->sendToClient($buffer);
						}
					}else{
						if($this->decodeBuffer($buffer, $this->client)){
							$this->sendToServer($buffer);
						}
					}
				}
			}
		}
	}

    private function decodeBuffer(string $buffer, Writer $writer) : bool{
		if(($packet = Packet::readDataPacket($buffer)) !== null){
			$writer->handleDataPacket($packet);
		}
		return true;
	}

	public function sendToServer(string $buffer) : void{
		$this->server->writePacket($buffer);
	}

	public function sendToClient(string $buffer) : void{
		$this->client->writePacket($buffer);
	}

	public function writePacket(string $buffer, string $host, int $port) : void{
		socket_sendto($this->socket, $buffer, strlen($buffer), 0, $host, $port);
	}

	public function getServer() : Server{
		return $this->server;
	}

	public function getClient() : Client{
		return $this->client;
	}
}