<?php

/*
 * 
 * This file will be called if the request was made with POST
 * 
 */


class API_FUNCTIONS extends API_BASE {
	
	public function execute(){
			
		//for extra security, define functions that can be called by the cleint
		$valid_functions = array("example1", "example2");
		
		$method = $this->getMethod();
		
		if(in_array($method, $valid_functions)){
			call_user_func(array($this, $method));
		}else{
			$this->respond('404', "Invalid method");
		}

	
	}
	
	public function example1(){
		//set required paramaters, can accept a string or array of strings
		$this->setRequiredParams(array('user_id', 'new_screen_name'));
		
		//get parameters
		$user_id = intval($this->getParam("user_id"));
		$name = $this->getParam("new_screen_name");
		
		//clean parameters
		$name = $this->clean($name);
		
		//Here you would run a query to update a username, update data, etc.
		
		
		$this->respond(200, array("updated" => 1, "new_screen_name" => $name));
	}
	
	
	public function example2(){
		//we can extract variables at they same time
		list($text, $post_id) = $this->setRequiredParams(array('text','post_id'));
		
		//clean vars
		$post_id = intval($post_id);
		$text = $this->clean($text);
		
		//Here you would run a query, post data into the database, etc.

		$this->respond(200, array("post_id" => $post_id, "inserted" => 1));
	}
	
	
}


?>