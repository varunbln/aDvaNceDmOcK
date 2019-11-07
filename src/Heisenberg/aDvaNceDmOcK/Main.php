<?php

declare(strict_types=1);

namespace Heisenberg\aDvaNceDmOcK;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\utils\TextFormat as C;

class Main extends PluginBase implements Listener{

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->db = new \SQLite3($this->getDataFolder() . "LastMessage.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS messages (player TEXT PRIMARY KEY COLLATE NOCASE, message TEXT);");
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		switch($command->getName()){
			case "mock":
				if(isset($args[0])) {
					$player = $sender->getServer()->getPlayer($args[0]);
					if($player === null){
						$sender->sendMessage(C::RED . "tHe reQuesTed PlaYeR iS nOt oNliNe");
						return true;
					} elseif($player->hasPermission("mock.immune")) {
						$sender->sendMessage(C::RED . "yOu CaNnOt MoCk tHiS pLaYer");
					} else {
						$name = $player->getName();
						$msg = $this->getLastMessage($name);
						
						if($msg !== null) {
							$length = strlen($msg);
							for($i = 0; $i < $length; $i++){
								$letter = strtolower($msg[$i]);
								if(mt_rand(0, 1) === 0){
									$letter = strtoupper($letter);
								}
								$msg[$i] = $letter;
							}
							$sender->chat($msg);
						} else {
							$sender->sendMessage(C::RED . "tHe rEqUesTed PlaYeR hAsn'T sEnT aNy MEssAgEs rEcEntLy");
					}
				}
				return true;
				}
				return true;
			break;
			case "mockme":
				if(empty($args)) {
					$sender->sendMessage(C::RED . "tYpE a MesSaGe tO mOck");
					return true;
				} else {
					$msg = implode(" ", $args);
				}
				$length = strlen($msg);
				for($i = 0; $i < $length; $i++){
					$letter = strtolower($msg[$i]);
					if(mt_rand(0, 1) === 0){
						$letter = strtoupper($letter);
					}
					$msg[$i] = $letter;
				}
				$sender->chat($msg);
				return true;
			break;
			case "mockall":
			if(isset($args[0])) { 
				if($args[0] === "on") {
					foreach($this->getServer()->getOnlinePlayers() as $player) {
						$perm = "mock.on";
						$player->addAttachment($this, $perm, true);
					}
					$sender->sendMessage(C::GREEN . "aLl mEssAgeS WiLl nOw LooK LikE tHis");
					return true;
				}
				if($args[0] === "off") {
					foreach($this->getServer()->getOnlinePlayers() as $player) {
						$perm = "mock.on";
						$player->addAttachment($this, $perm, false);
					}
					$sender->sendMessage(C::GREEN . "aLl mEssAgeS WiLl nO LonGer LooK LikE tHis");
					return true;
				}
				return true;
			} else {
				$sender->sendMessage(C::AQUA . "Do /mockall <on/off>");
				return true;
			}
			break;
			case "mocking":
			if(isset($args[0])) { 
				if($args[0] === "on") {
						$perm = "mock.on";
						$sender->addAttachment($this, $perm, true);
						$sender->sendMessage(C::GREEN . "aLl yOuR cHaT mEssAgeS WiLl nOw LooK LikE tHis");
						return true;
				}
				if($args[0] === "off") {
						$perm = "mock.on";
						$sender->addAttachment($this, $perm, false);
						$sender->sendMessage(C::GREEN . "aLl yOuR cHAt mEssaGeS WiLl nO LonGer LooK LikE tHis");
						return true;
				}
				return true;
			} else {
				$sender->sendMessage(C::AQUA . "Do /mocking <on/off>");
				return true;
			}
			break;
		}
	}

	public function getLastMessage($playername) {
		$result = $this->db->query("SELECT message FROM messages WHERE player = '$playername';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
		if(empty($resultArr)) {
			return null;
		}
        return (string)$resultArr["message"];
	}

	public function onPlayerChat(PlayerChatEvent $ev){
		
		$msg = $ev->getMessage();
		$rawmsg = strtolower($msg);
		$player = $ev->getPlayer();
		$playername = $ev->getPlayer()->getName();
		
		$stmt = $this->db->prepare("INSERT OR REPLACE INTO messages (player, message) VALUES (:player, :message);");
        $stmt->bindValue(":player", $playername);
        $stmt->bindValue(":message", $rawmsg);
        $stmt->execute();
		
		$length = strlen($msg);
		if($player->hasPermission("mock.on")) {
			for($i = 0; $i < $length; $i++){
				$letter = strtolower($msg[$i]);
				if(mt_rand(0, 1) === 0){
					$letter = strtoupper($letter);
				}
				$msg[$i] = $letter;
			}
			$ev->setMessage($msg);
		}
	}
}
