<?php

use pocketmine\network\mcpe\protocol\DataPacket;

class Server extends Writer{

    public $data;

    public function handleDataPacket(DataPacket $packet) : bool{
        return true;
    }

    public function getName() : ?string{
        return "Proxy by Correct88004216";
    }

    public function getProtocol() : ?string{
		return isset($this->data[1]) ? $this->data[1] : null;
	}

	public function getVersion() : ?string{
		return isset($this->data[2]) ? $this->data[2] : null;
	}
}