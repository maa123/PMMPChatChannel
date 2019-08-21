<?php
namespace ChatChannel;

class JsonFile {
	public $data;
	protected $path;
	function __construct($path, $init = null) {
		$this->data = $init;
		$this->path = $path;
		if(!file_exists($path)){
			$this->save();
			return;
		}
		$this->data = json_decode(file_get_contents($path), true);
	}
	public function save() {
		file_put_contents($this->path, json_encode($this->data));
	}
}