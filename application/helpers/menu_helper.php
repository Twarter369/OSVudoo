<?php

function default_template_nav($current){
	if(isset($_SESSION['logged_in_user'])){
		$user = new User();
		$user = $user->cast('user',$_SESSION['logged_in_user']);
	}
	$html = "<div id='templatemo_menu'>";
    $html .= "<ul>";
	
	if(isset($user) && $user->is_admin()){
		/*User is Admin*/
    	$html .= "<li><a href='/homes/spaceships' class='current'>Admin</a></li>";
		$html .= "<li><a href='".get_home_link()."' target='_parent'>Home</a></li>";
		$html .= "<li><a href='/projects/viewall' target='_parent'>Projects</a></li>";
		$html .= "<li><a href='#' target='_parent'>Statistics</a></li>";
		$html .= "<li><a href='#' target='_parent'>Logs</a></li>";
	}else{
		$html .= "<li><a href='".get_home_link()."' class='current'>Base</a></li>";
		$html .= "<li><a href='#' target='_parent'>Gallery</a></li>";
    	$html .= "<li><a href='#'>Contact</a></li>";
	}
    $html .= "</ul>";
    $html .= "</div> <!-- end of templatemo_menu -->";
		
	return $html;
}

function get_home_link(){
	if(isset($_SESSION['logged_in_user'])){
		return '/projects/viewall';	
	}else{
		return '/homes/login';	
	}
}
function super_nav_bar(){
	return "<div id='templatemo_menu'>
            <ul>
                <li><a href='#' class='current'>Home</a></li>
                <li><a href='http://www.greeney.com/page/1' target='_parent'>Templates</a></li>
                <li><a href='http://www.flashmo.com' target='_parent'>Flash</a></li>
                <li><a href='http://www.koflash.com' target='_parent'>Gallery</a></li>
                <li><a href='#'>Contact</a></li>
            </ul>    	
        </div> <!-- end of templatemo_menu -->";
}
?>