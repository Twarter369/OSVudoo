<?php

class Image extends Model {
	var $hasOne = array('user'=>'user');
	//var $hasMany = null;
	
	public function __construct() {
		$this->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
		$this->_table = 'images';
	}
	
	public function getURL(){
		return IMAGE_ROOT."/".$this->Image->location;
	}
}

?>