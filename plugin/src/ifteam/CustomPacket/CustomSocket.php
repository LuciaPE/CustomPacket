<?php

namespace ifteam\CustomPacket;

class CustomSocket {
	
	protected $logger, $interface, $port;
	
	public function __construct($logger, $interface = '0.0.0.0', $port = 19131){
		$this->logger = $logger;
		$this->interface = filter_var($interface, FILTER_VALIDATE_IP)? $interface : '0.0.0.0';
		$this->port = $port;
		$this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if (@socket_bind ( $this->socket, $this->interface, $this->port ) === true) {
			socket_set_option ( $this->socket, SOL_SOCKET, SO_REUSEADDR, 0 );
			@socket_set_option ( $this->socket, SOL_SOCKET, SO_RCVBUF, 1024 * 1024 );
			@socket_set_option ( $this->socket, SOL_SOCKET, SO_SNDBUF, 1024 * 1024 * 8 );
		} else {
			$this->logger->critical ("*** FAILED TO BIND TO " . $this->interface . ":" . $this->port . "!", true, true, 0 );
			$this->logger->critical ("*** Perhaps a server is already running on that port?", \true, \true, 0);
		}
		socket_set_nonblock ( $this->socket );
		socket_set_option($this->socket,SOL_SOCKET,SO_RCVTIMEO,array("sec"=>1, "usec"=>0));
		$this->logger->info("CustomSocket: Done loading.");
	}
	
	public function sendPacket($buffer, $address, $port, $scream = false){
		if(!filter_var($address, FILTER_VALIDATE_IP)) return false;
		return $scream ? socket_sendto($this->socket, $buffer, strlen($buffer), 0, $address, $port) : 
									@socket_sendto($this->socket, $buffer, strlen($buffer), 0, $address, $port);
	}
	
	public function recvPacket(&$buffer, &$address, &$port, $scream = false){
		return $scream ? socket_recvfrom($this->socket, $buffer, @socket_get_option($this->socket, SOL_SOCKET, SO_RCVBUF), 0, $address, $port) : 
									@socket_recvfrom($this->socket, $buffer, @socket_get_option($this->socket, SOL_SOCKET, SO_RCVBUF), 0, $address, $port);
	}
	
	public function close(){
		socket_close($this->socket);
	}
	
}
?>
