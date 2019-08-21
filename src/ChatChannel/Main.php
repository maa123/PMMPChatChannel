<?php
namespace ChatChannel;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerLoginEvent;


use pocketmine\Player;

use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;

use pocketmine\Server;

use ChatChannel\CCCommand;
use ChatChannel\JsonFile;

class Main extends PluginBase implements Listener{
	private $Players;
	private $Channels;

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getCommandMap()->registerAll("ChatChannel", [new CCCommand($this)]);
		
		$dF = $this->getDataFolder();
		$this->Channels = new JsonFile($dF."channels.json", []);
		$this->Players  = new JsonFile($dF."players.json", []);//Players->data["Name"] = ['channle', ['true/false(viewGlobal)']]
	}
	public function onDisable(){
		$this->Channels->save();
		$this->Players->save();
	}
	public function GetChannels(){
		return $this->Channels->data;
	}
	public function GetPlayerChannel($name){
		if(isset($this->Players->data[$name]) && $this->Players->data[$name][0] === null){
			return "チャンネルに参加していません";
		}
		return $this->Players->data[$name]." に参加しています";
	}
	public function AddChannel($name){
		if(in_array($name, $this->Channels->data)){
			return "既に存在するチャンネル名です";
		}
		$this->Channels->data[] = $name;
		return "チャンネル名:".$name. "を追加しました";
	}
	public function RemoveChannel($name){
		$key = array_search($name, $this->Channels->data);
		if($key === false){
			return "存在しないチャンネル名です";
		}
		foreach ($this->Players->data as $key => $pl) {
			if($pl[0] === $name){
				$this->Players->data[$key] = [null, [true]];
			}
		}
		$this->Channels->data = array_splice($this->Channels->data, $key, 1);
		return "チャンネル名:".$name. "を削除しました";
	}
	public function JoinChannel($name, $ch){
		$key = array_search($ch, $this->Channels->data);
		if($key === false){
			return "存在しないチャンネル名です";
		}
		$this->Players->data[$name][0] = $ch;
		return "チャンネル名:".$ch. "に参加しました";
	}
	public function LeaveChannel($name){
		if($this->Players->data[$name][0] !== null){
			return "チャンネルに参加していません";
		}
		$this->Players->data[$name] = [null, [true]];
		return "チャンネルへの参加を停止しました";
	}
	public function GlobalSettings($name, $d){
		if($this->Players->data[$name][0] !== null){
			return "チャンネルに参加しているためグローバルチャットの設定変更はできません";
		}
		if($d){
			$this->Players->data[$name][1][0] = true;
			return "グローバルチャットの表示を有効化しました";
		}else{
			$this->Players->data[$name][1][0] = false;
			return "グローバルチャットの表示を無効化しました";
		}
	}
	public function onChat(PlayerChatEvent $event){
		$event->getMessage();
		$name = $event->getPlayer->getName();
		$rs = $event->getRecipients();
		if($this->Players->data[$name][0] !== null){
			foreach ($rs as $key => $pl) {
				$nm = $pl->getName();
				if($this->Players->data[$nm][0] !== $this->Players->data[$name][0]){
					unset($rs[$key]);
				}
			}
			$event->setRecipients(array_values($rs));
		}else{
			foreach ($rs as $key => $pl) {
				$nm = $pl->getName();
				if(!$this->Players->data[$nm][1][0]){
					unset($rs[$key]);
				}
			}
			$event->setRecipients(array_values($rs));
		}
	}
	public function onLogin(PlayerLoginEvent $event){
		$name = $event->getPlayer()->getName();
		if(!isset($this->Players->data[$name])){
			$this->Players->data[$name] = [null, [true]];
		}
	}
}