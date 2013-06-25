<?php
class Controller {

	protected $_model;
	protected $_controller;
	protected $_action;
	protected $_template;
	public $doNotRender =false;
	public $urlHelper;
	public $logged_in_user;
	public $exempt_from_login;//Array of Controllers that don't have to be logged in to access. Only applies if REQUIRE_LOGIN = true
	
	function __construct($model, $controller, $action) {
		$inflect = new Inflection();
		require_once (ROOT . DS . 'application' . DS . 'helpers' . DS . 'url_helper.php');
		$this->_controller = $controller;
		$this->_action = $action;
		$this->_model = $model;
		$this->exempt_from_login = array('homes');//add the controller that handles the login here and any other exempt controllers
		$this->$model = new Model();
		
		$this->redirect_if_not_logged_in();
		
		/*Only render templates for non-ajax requests*/
		if(!$this->is_ajax_request()&&$this->doNotRender == false){
			$this->_template = new Template($controller,$action);
		}else{
			$this->_template = null;
		}
		
		$this->urlHelper = new UrlHelper();
		$this->set_user_object();
		$this->parse_request();
	}


	function upload_file($file,$rewrite=1){
		if ($file["error"] > 0){
			return false;
		}else{
			if (file_exists(IMAGE_ROOT . "/" . $file["name"])){
				if($rewrite == 2){
					//Keep both files by appending a rand number. not full proof
					$file_parts = explode(".",$file['name']);
					$num = rand(3,99999);
					$new_name = $file_parts[0] . '_' . $num . '.' . $file_parts[1];
					$file['name'] = $new_name;
				}elseif($rewrite == 1){
					if(move_uploaded_file($file["tmp_name"], ROOT . DS . 'public' . DS . 'img' . DS . $file["name"])){
						return true;
					}else{
						return false;
					}
				}else{
					return false;	
				}
			}else{
				if(move_uploaded_file($file["tmp_name"], ROOT . DS . 'public' . DS . 'img' . DS . $file["name"])){
					return true;
				}else{
					return false;
				}
			}			
		}	
	}
	
	function is_ajax_request(){
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			/*Request was made via ajax*/
			return true;
		}else{
			return false;	
		}	
	}

	function set($name,$value) {
		$this->_template->set($name,$value);
	}

	function set_user_object(){
		//If the user is set Pass a User Object to the Controller
		
	}
	
	function parse_request(){
		$kv_array = array();
		/*Get query and send to $_REQUEST */
		$raw = parse_url($_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]);
		if(isset($raw['query'])){
			$raw = $raw['query'];
			$pairs = explode("&",$raw);
			foreach($pairs as $pair){
				$kv = explode("=",$pair);
				$_REQUEST[$kv[0]] = $kv[1];
			}
		}	
	}
	
	function redirect_if_not_logged_in(){
		//If no user is set then redirect to the home	
		$inflect = new Inflection();
		if(REQUIRE_LOGIN && !in_array(strtolower($inflect->pluralize($this->_model)),$this->exempt_from_login) && !isset($_SESSION['logged_in_user'])){
			$this->_template = new Template();
			$this->_template->redirect_to(array('controller'=>'homes','action'=>'login'));
		}
	}
	
	function __destruct() {
		if(isset($this->_template)){
			$this->_template->render();
		}
	}

	
	/** Cast an object to another object **/
	function cast($destination, $sourceObject)
	{
		if (is_string($destination)) {
			$destination = new $destination();
		}
		$sourceReflection = new ReflectionObject($sourceObject);
		$destinationReflection = new ReflectionObject($destination);
		$sourceProperties = $sourceReflection->getProperties();
		foreach ($sourceProperties as $sourceProperty) {
			$sourceProperty->setAccessible(true);
			$name = $sourceProperty->getName();
			$value = $sourceProperty->getValue($sourceObject);
			if ($destinationReflection->hasProperty($name)) {
				$propDest = $destinationReflection->getProperty($name);
				$propDest->setAccessible(true);
				$propDest->setValue($destination,$value);
			} else {
				$destination->$name = $value;
			}
		}
		#print_r($destination);
		return $destination;
	}
}
