<?php
namespace ChatChannel;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class CCCommand extends Command {
	public function __construct(Plugin $_this){
		parent::__construct("cc", "チャンネルの操作を行います", "/cc [list|join|leave|gset|op]");
		$this->_this = $_this;
	}
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!isset($args[0])){
			return false;
		}
		if($args[0] === 'list'){
			$list = $this->_this->GetChannels();
			foreach ($list as $ch) {
				$sender->sendMessage("[CC] ".$ch);
			}
			return true;
		}else if($args[0] === 'join'){
			if(!isset($args[1])){
				$sender->sendMessage("[CC] チャンネル名が指定されていません");
				return false;
			}
			$res = $this->_this->JoinChannel($sender->getPlayer()->getName(), $args[1]);
			$sender->sendMessage("[CC] ".$res);
			return true;
		}else if($args[0] === 'leave'){
			$res = $this->_this->LeaveChannel($sender->getPlayer()->getName());
			$sender->sendMessage("[CC] ".$res);
			return true;
		}else if($args[0] === 'gset'){
			if(!isset($args[1])){
				$sender->sendMessage("[CC] on/offが指定されていません");
				return false;
			}
			$flag = strtolower($args[1]);
			switch ($flag) {
				case 'on':
				case 'true':
					$flag = true;
					break;
				case 'off':
				case 'false':
					$flag = false;
				default:
					$flag = true;
					break;
			}
			$res = $this->_this->GlobalSettings($sender->getPlayer()->getName(), $flag);
			$sender->sendMessage("[CC] ".$res);
			return true;
		}else if($args[0] === 'op'){
			if(!$sender->getPlayer()->isOp()){
				$sender->sendMessage("[CC] このコマンドを実行するための権限がありません");
				return false;
			}
			if(!isset($args[1]) || !isset($args[2])){
				$sender->sendMessage("[CC] 操作が指定されていません");
				$sender->sendMessage("[CC] /cc op [add|remove] team");
				return false;
			}
			if($args[1] === 'add'){
				$res = $this->_this->AddChannel($args[2]);
				$sender->sendMessage("[CC] ".$res);
			}else if($args[1] === 'remove'){
				$res = $this->_this->RemoveChannel($args[2]);
				$sender->sendMessage("[CC] ".$res);
			}else{
				$sender->sendMessage("[CC] 指定された操作は存在しません");
			}
		}
		
		return false;
	}
}