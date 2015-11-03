<?php

namespace PingPong;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\PluginCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use ifteam\CustomPacket\event\CustomPacketReceiveEvent;
use ifteam\CustomPacket\CPAPI;
use ifteam\CustomPacket\DataPacket;

class PingPong extends PluginBase implements Listener {
	public function onEnable() {
		$this->registerCommand ( "ping", "pingpong", "CustomPacket PingPong Command", "/ping <ip:port> <string>" );
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
	}
	public function onCommand(CommandSender $player, Command $command, $label, array $args) {
		if (! isset ( $args [1] )) {
			$player->sendMessage ( TextFormat::RED . "/ping <ip:port> <string>" );
			return true;
		}
		
		$address = explode ( ":", $args [0] );
		if (! isset ( $address [1] )) {
			$player->sendMessage ( TextFormat::RED . "/ping <ip:port> <string>" );
			return true;
		}
		
		$data = json_encode ( [ 
				"pingpong" , $args [1] 
		] );
		$packet = new DataPacket ( $address [0], $address [1], $data );
		echo "normal json\n";
		var_dump($data);
		CPAPI::sendPacket ( $packet );
		$this->getLogger ()->info ( "Packet Sent!" );
		return true;
	}
	public function onCustomPacketReceiveEvent(CustomPacketReceiveEvent $event) {
		echo "Received!\n";
		$data = json_decode ( $event->getPacket ()->data);
		var_dump($data);
		if (! is_array ( $data )){
			echo "array is not!\n";
			return;
		}
		if($data [0] != "pingpong"){
			echo "passpacket wrong!\n";
			return;
		}
		//if (! is_array ( $data ) or $data [0] != "pingpong")
		//	return;
		$this->getLogger ()->info ( "Received: " . $data );
		$event->getPacket ()->printDump ();
	}
	/**
	 * Register the plug-in command
	 *
	 * @param string $name        	
	 * @param string $permission        	
	 * @param string $description        	
	 * @param string $usage        	
	 */
	public function registerCommand($name, $permission, $description = "", $usage = "") {
		$commandMap = $this->getServer ()->getCommandMap ();
		$command = new PluginCommand ( $name, $this );
		$command->setDescription ( $description );
		$command->setPermission ( $permission );
		$command->setUsage ( $usage );
		$commandMap->register ( $name, $command );
	}
}

?>