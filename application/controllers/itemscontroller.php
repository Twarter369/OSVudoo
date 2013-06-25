<?php

class ItemsController extends Controller {

	function view($id = null,$name = null) {
		$item = new Item();
		$this->set('title',$name.' - My Todo List App');
		$this->set('todo',$item->select($_REQUEST['id']));

	}
	
	function viewall() {
		$item = new Item();
		$this->set('title','All Items - My Todo List App');
		$this->set('todo',$item->selectAll());
	}
	
	function add() {
		$todo = $_POST['todo'];
		$this->set('title','Success - My Todo List App');
		$this->set('todo',$this->Item->query('insert into items (item_name) values (\''.mysql_real_escape_string($todo).'\')'));	
	}
	
	function delete($id = null) {
		$this->set('title','Success - My Todo List App');
		$this->set('todo',$this->Item->query('delete from items where id = \''.mysql_real_escape_string($id).'\''));	
	}

}
