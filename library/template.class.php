<?php
class Template {

	protected $variables = array();
	protected $_controller;
	protected $_action;

	function __construct($controller,$action) {
		$this->_controller = $controller;
		$this->_action = $action;
	}

	function redirect_to($options) {
		if(is_string($options)){
			$url = $options;
			if($options == "back") {
            	$url = $_SERVER["HTTP_REFERER"];
			} else {
				$url = $options;
			}
		}elseif(is_array($options)){
			$url = SITE_ROOT . $options['controller']."/".$options['action'];
			if(count($options>3)){
				unset($options['controller']);
				unset($options['action']);
				$query_string = "?";
				foreach($options as $key=>$value){
					$query_string .= $key."=".$value."&";
				}
				$query_string = urlencode(substr($query_string,0,strlen($query_string)-1));
				$url .= $query_string;
			}
		}
		
        if(headers_sent()) {
            echo "<html><head><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$url."\"></head></html>";
        } else {
            header("Location: ".$url);
        }        
  
        exit;            
    }

	/** Set Variables **/
	function set($name,$value) {
		$this->variables[$name] = $value;
	}

	/** Display Template **/

    function render() {
		extract($this->variables);
			$inflector = new Inflection();
			if (file_exists(ROOT . DS . 'application' . DS . 'views' . DS . strtolower($inflector->pluralize($this->_controller)) . DS . '_header.php')) {
				include (ROOT . DS . 'application' . DS . 'views' . DS . strtolower($inflector->pluralize($this->_controller)) . DS . '_header.php');
			} else {
				include (ROOT . DS . 'application' . DS . 'views' . DS . 'header.php');
			}

        include (ROOT . DS . 'application' . DS . 'views' . DS . strtolower($inflector->pluralize($this->_controller)) . DS . $this->_action . '.php');		 

			if (file_exists(ROOT . DS . 'application' . DS . 'views' . DS . strtolower($inflector->pluralize($this->_controller)) . DS . '_footer.php')) {
				include (ROOT . DS . 'application' . DS . 'views' . DS . strtolower($inflector->pluralize($this->_controller)) . DS . '_footer.php');
			} else {
				include (ROOT . DS . 'application' . DS . 'views' . DS . 'footer.php');
			}
    }

	function render_partial($partial_name) {
		extract($this->variables);

		if (file_exists(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . "_" . $partial_name . '.php')) {
			include (ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . "_" . $partial_name . '.php');
		}else{
			echo ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . "_" . $partial_name . '.php not found<br>';
		}
    }
}
