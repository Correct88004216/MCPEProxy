<?php

class Address{
    
    public $ip;
    public $port;
    public $version;

    public function __construct(string $address, int $port, int $version = 4){
        $this->ip = $address;
        $this->port = $port;
        $this->version = $version;
    }

    public function __toString(){
		return $this->ip . ":" . $this->port;
	}

	public function toString() : string{
		return $this->__toString();
	}

	public function equals(Address $address) : bool{
		return $this->ip === $address->ip and $this->port === $address->port and $this->version === $address->version;
	}
}