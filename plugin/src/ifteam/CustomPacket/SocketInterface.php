<?php

namespace ifteam\CustomPacket;

use pocketmine\Server;
use ifteam\CustomPacket\event\CustomPacketPreReceiveEvent;
use ifteam\CustomPacket\event\CustomPacketReceiveEvent;
use ifteam\CustomPacket\event\CustomPacketSendEvent;

class SocketInterface{
	
	private $internalThreaded;
	private $externalThreaded;
	private $server;
	private $socket;
	
	const CACHE_VALID_TIME_LIMIT = 1800;
	const BLOCK_TIME_SECONDS = 600;
	
	public function __construct(Server $server, $port){
		$this->internalThreaded = new \Threaded();
		$this->externalThreaded = new \Threaded();
		$this->server = $server;
		$this->socket = new CustomSocket($this->internalThreaded, $this->externalThreaded, $this->server->getLogger(), $port, $this->server->getIp() === "" ? "0.0.0.0" : $this->server->getIp());
	}
	
	public function process(){
		$work = false;
		$this->pushInternalQueue([Info::SIGNAL_TICK]);
		if($this->handlePacket()){
			$work = true;
			while($this->handlePacket());
		}
		return $work; //For future use. Not now.
	}
	
	public function handlePacket(){
		if(($packet = $this->readMainQueue()) instanceof DataPacket){
			Server::getInstance()->getPluginManager()->callEvent($ev = new CustomPacketPreReceiveEvent(clone $packet));
			if(!$ev->isCancelled()) Server::getInstance()->getPluginManager()->callEvent($ev = new CustomPacketReceiveEvent(clone $packet));
			return true;
		}
		return false;
	}
	
	public function shutdown(){
		$this->pushInternalQueue([Info::SIGNAL_SHUTDOWN]);
	}
	
	public function sendPacket(DataPacket $packet){
		Server::getInstance()->getPluginManager()->callEvent($ev = new CustomPacketSendEvent($packet));
		if(!$ev->isCancelled()) $this->pushInternalQueue([Info::PACKET_SEND, $packet]);
	}
	
	public function blockAddress($address, $seconds){
		$this->pushInternalQueue([Info::SIGNAL_BLOCK, [$address, time() + $seconds]]);
	}
	
	public function unblockAddress($address){
		$this->pushInternalQueue([Info::SIGNAL_UNBLOCK, $address]);
	}
	
	/**
	 * @deprecated
	 */
	 
	public function pushMainQueue(DataPacket $packet){
		//$this->exteranlThreaded[] = json_encode($buffer);
		$this->exteranlThreaded[] = serialize($buffer);
	}
	
	public function readMainQueue(){
		//return json_decode($this->externalThreaded->shift());
		return unserialize($this->externalThreaded->shift());
	}
	
	public function pushInternalQueue(array $buffer){
		//$this->internalThreaded[] = json_encode($buffer);
		$this->internalThreaded[] = serialize($buffer);
	}
	
	public function readInternalQueue(){
		//return json_decode($this->internalThreaded->shift());
		return unserialize($this->internalThreaded->shift());
	}
	
}
