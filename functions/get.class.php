<?php

/*
 * 
 * This file will be called if the request was made with GET
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
		//set required paramaters, it returns a string or a list of variables, depending on what is passed to it
		$user_id = $this->setRequiredParams('user_id');
		$user_id = intval($user_id);
		
		//Here you would run a query, get data, etc.	
		//$res = $this->query("SELECT blah FROM table WHERE user_id='$user_id' LIMIT 1");
			
		$username = "JohnJones";
		$full_name = "John Robert Jones";
		
		$this->respond(200, array("username" => $username, "full_name" => $full_name));
	}
	
	
	public function example2(){
		//we can extract variables at they same time that we've required them
		list($text, $post_id) = $this->setRequiredParams(array('text','post_id'));
		
		//clean vars
		$post_id = intval($post_id);
		$text = $this->clean($text);
		
		//Here you would run a query, post data into the database, etc.
				
		$username = "testing";
		$full_name = "John Robert Jones";
		
		$this->respond(200, array("username" => $username, "full_name" => $full_name));
	}
	
	
	
}


?>