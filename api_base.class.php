<?php
class API_BASE {
	
	/*
	 * CONFIGURATION OPTIONS:
	 * 
	 */	
	
	//boolean; set to true to log/save the call request into the API HISTORY table
		//this can be set dynamically with logCall(boolean)
	private $log_call = true;
	
	//strings; database connection parameters:
	private $db_host = "localhost", $db_user = "crambu_api",$db_password = "6b_I+f,-@4Xi", $db_name = "crambu_apiFramework";
	
	
	//strings; name of the parameters that hold the name method and format, i.e. - ?method=username&format=json
	private $method_param_name = "method";
	private $format_param_name = "format";
	
	
	//strings; name of the parameters that hold the application auth variables, i.e. - ?app_id=1&app_secret=bhvjklsdhvlau
	private $app_id_param_name = "app_id";
	private $app_secret_param_name = "app_secret";
	
	
	//strings; name of the parameters that hold the user auth variables, i.e. - ?app_id=access&app_secret=bhvjklsdhvlau	
	private $access_token_param_name = "access_token";
	private $access_token_param_secret = "access_secret";	
		
		
	//string; EXTERNAL name of the method (or 'function') that  create a new session token and return the data to the app, i.e, ?method=access_token
	private $access_token_function_name = "access_token";
	
	
	
	
	/*
	 * FUTURE: move parameter names to an array
	private $param_names = array(
		"method" => "method", //function name to be call
		"format" => "format", //format returned in
		
		"app_id" => "app_id", //
		"app_secret" => "app_secret",
		
		"access_token" => "access_token",
		"access_secret" => "access_secret"
	);
	 */	
	
	
	
	
	
	
	
	
	 //No need to edit variables below this line 
	private $method, $request_data_method, $request_data, $response_format = null;//function to be called


	private $user_id, $app_id, $access_token = null;//auth vars
	
	public function __construct(){
		
		//connect to database
		mysql_connect($this->db_host, $this->db_user, $this->db_password) or $this->respond(503 ,"Database connection broken");
		mysql_select_db($this->db_name) or $this->respond(503, "Database connection broken");		
		
		//setup method and data
		$request_method = $this->determineRequestMethod(false);//false to prevent loading another function doc
		$this->request_data_method = $request_method;//set request method var
		$this->request_data = $request_method == 'post' ? $_POST : $_GET;//default to GET

		//set response format, defaults to json
		$this->response_format = $this->getParam($this->format_param_name) == 'xml' ? 'xml' : 'json';

		//retreives the method parameter
		$method = $this->getParam($this->method_param_name);
				
		if(empty($method)){//in the client sence, method is the function they want to call
			$this->respond(404, 'Method was not declcared');
		}else{
			$this->method = strtolower($method);//define the method called in lowercase form
		}
		
		//built in functionality:
		if($this->method == $this->access_token_function_name){
			$this->createAccessToken();
		}elseif($this->method == "request_auth"){
			$this->requestingAuth();
		}


		if($this->log_call){
			$this->start_time = $this->getTime();
		}


	}
		
	public function __destruct(){
		if($this->log_call){
			
			if(isset($this->start_time)){
				$end = $this->getTime();
				$time = ($end - $this->start); 
			}else{
				$time = null;
			}
			
			$method = $this->clean($this->method);
			$code = intval($this->response_code);
			$app_id = $this->getAppId();
			$user_id = $this->getUserId();
			
			$this->query("INSERT DELAYED INTO saved_api_log_history (`datetime_called`,`method`,`response_code`,`time_response`,`app_id`,`user_id`)
			VALUES (NOW(),'$method','$code','$time','$app_id','$user_id')");

				
		}
		mysql_close();
	}
	
	
	public function query($query){
		$result = mysql_query($query) or $this->respond(503, 'Database query error:'.mysql_error());
		return $result;
	}
	
	public function clean($var){
		return mysql_real_escape_string($var);
	}
	
	public function getParam($param){
		return $this->request_data[$param];
	}
	
	public function getData(){
		return $this->request_data;
	}
	
	public function getResponseMethod(){
		return $this->request_data_method;
	}
	
	public function getFormat(){
		return $this->response_format;
	}
	
	public function getMethod(){
		return $this->method;
	}

	public function getAppId(){
		return $this->app_id;
	}
	
	public function getUserId(){
		return $this->user_id;
	}

	public function logCall($log = true){
		$this->log_call = $log;
	}
	

	public static function determineRequestMethod($load = true){
		
		$request_data_method = strtolower($_SERVER['REQUEST_METHOD']);  
		
		$require = $request_data_method == 'post' ? 'post' : 'get';
		
		if($load){
			require("functions/".$require.".class.php");
		}
		
		return $require;
	}
	
	
	//method to establish required expected paramteres.
	//@$param -> required ->
	public function setRequiredParams($param){
		if(is_array($param)){//check if multiple params
			$return_data = array();
			foreach($param as $param_value){
				$this->requireParam($param_value);
				array_push($return_data, $this->getParam($param_value));
			}
			return $return_data;
		}else{//single param
			$this->requireParam($param);
			return $this->getParam($param);
		}
	}
	
	
	//return a particular param
	private function requireParam($param){
		$value_check = $this->getParam($param);
		if(empty($value_check)){//check to ensure param exists
			$this->respond(400, "Required parameter of '".$param."' is missing or empty");
		}
	}
	
	
	
	
	/*
	 * Authentication methods
	 * 		requireAuthLevel
	 * 		setAppId
	 * 		setUserId
	 * 
	 */
	
	
	
	
	//method to set the security level of the whole application, default is max
	public function setSecurityLevel($level){
		$this->security_level = $level;
		return true;
	}

	//method to get the security level of the whole application, default is max
	public function getSecurityLevel($level){
		return $this->security_level;
	}	

	//method to establish level of authentication required
	public function requireAuthLevel($level = 'userAuth'){
		if($level == 'userAuth'){
			$this->validateToken();
		}elseif($level == 'appAuth'){
			$this->validateApp();
		}elseif($level == 'none'){
			//do nothing
		}else{
			//throw an error, auth is too important to do nothing on accident
			$this->respond(500, "setAuthLevel error: $level does not exists [options: userAuth, appAuth, none]");
		}
	}
	
	/*
	 * 	helpers in setting app and user ids
	 * 		created for validation functions
	 */ 
	
	private function setAppId($id){
		$this->app_id = $id;
	}
	
	private function setUserId($id){
		$this->user_id = $id;
	}
	
	
	
	/*
	 * validateToken
	 * 		is called on userAuth requirement
	 */
	
	private function validateToken(){
		//require and get params
		list($token, $secret) = $this->setRequiredParams(array("access_token", "access_secret"));
		
		//clean vars
		$token = $this->clean($token);
		$secret = $this->clean($secret);
		
		//query
		$result = $this->query("SELECT user_id, app_id FROM api_sessions WHERE `token`='$token' AND `token_secret`='$secret' AND UNIX_TIMESTAMP(`expires_on`) > UNIX_TIMESTAMP(NOW()) LIMIT 1");
		
		//ensure we have a result
		if(mysql_num_rows($result) == 0){
			$this->respond(404, "Invalid userAuth access_token and access_secret combination");
		}
		
		//get data
		$row = mysql_fetch_array($result);
		$app_id = intval($row["app_id"]);
		$user_id = intval($row["user_id"]);
		
		//save data
		$this->setAppId($app_id);
		$this->setUserId($user_id);
	}
	
	
	
	/*
	 * validateApp
	 * 		is called on appAuth requirement
	 */
	
	private function validateApp(){
		//require and get params
		list($app_id, $app_secret) = $this->setRequiredParams(array($this->app_id_param_name, $this->app_secret_param_name));		
		
		//clean vars
		$app_id = intval($app_id);
		$app_secret = $this->clean($app_secret);
		
		//query
		$result = $this->query("SELECT app_id FROM apps WHERE app_id='$app_id' AND `app_secret`='$app_secret' LIMIT 1");
	 	
		//ensure we have a result
		if(mysql_num_rows($result) == 0){
			$this->respond(404, "Invalid appAuth ".$this->app_id_param_name." and ".$this->app_secret_param_name." combination");
		}		
		
		//save results
	 	$this->setAppId($app_id);
		return $app_id;
	}
	
	
	
	private function createAccessToken(){
		//auth the app first		
		$app_id = $this->validateApp();
		
		//clean the token
		$token = $this->setRequiredParams("auth_token");
		$token = $this->clean($token);
		
		//query
		$result = $this->query("SELECT user_id FROM api_loggedin_tokens WHERE `app_id`='$app_id' AND `token`='$token' AND UNIX_TIMESTAMP(`expires_on`) > UNIX_TIMESTAMP(NOW()) LIMIT 1 ");
		//ensure we have a result
		if(mysql_num_rows($result) == 0){
			$this->respond(404, "Invalid auth_token and app_id combination, or token is expired");
		}
		
		//get the user_id
		$row = mysql_fetch_array($result);
		$user_id = intval($row["user_id"]);
		
		//create the token
		$token = $this->genRandomString(40);
		$secret = $this->genRandomString(20);
		
		$this->query("INSERT INTO `api_sessions` (`token`,`token_secret`,`user_id`,`app_id`,`expires_on`) VALUES ('$token','$secret','$user_id','$app_id', DATE_ADD(NOW(), INTERVAL 6 HOUR) )");
	
		$this->respond(200, array("access_token" => $token, "access_secret" => $secret));

	}
	
	
	
	
	
	
	/*
	private function requestingAuth(){
		$app_id = $this->requireParam("app_id");
		
		if($this->userLoggedIn()){
			
			//a user is logged in
			$result = $this->query("SELECT ");
		}else{
			
			//a user is not logged in
			header("Location: authUser.php");
			die();
		}
		
	}
	
	
	private function userLoggedIn(){
		return false;
	}	
	 *
	 */
	
	
	//respond with the appropriate data
	public function respond($status = 200, $body){
		
		if($status != 200){
			$response = array("error" => 1, "message" => $body);
		}else{
			$response = array("error" => 0, "data" => $body);
		}
		
		$this->response_code = $status;
		
		//set the response code
		$responseCodeText = $this->getStatusCodeMessage($status);
		header("HTTP/1.0 ".$status." ".$responseCodeText, true, $status);
		
		if($this->response_format == 'json'){
			header('Content-type: application/json');
			echo json_encode($response);
		}else{
			
			header ("Content-Type:text/xml"); 
			// creating object of SimpleXMLElement
			$xml = new SimpleXMLElement("<?xml version=\"1.0\"?><response></response>");
			
			// function call to convert array to xml
			$this->array_to_xml($response,$xml);
			
			echo $xml->asXML();
		}

		die();
		
	}
	
	
		
	
	public static function getStatusCodeMessage($status){
		// these could be stored in a .ini file and loaded
		// via parse_ini_file()... however, this will suffice
		// for an example
		$codes = Array(
		    100 => 'Continue',
		    101 => 'Switching Protocols',
		    200 => 'OK',
		    201 => 'Created',
		    202 => 'Accepted',
		    203 => 'Non-Authoritative Information',
		    204 => 'No Content',
		    205 => 'Reset Content',
		    206 => 'Partial Content',
		    300 => 'Multiple Choices',
		    301 => 'Moved Permanently',
		    302 => 'Found',
		    303 => 'See Other',
		    304 => 'Not Modified',
		    305 => 'Use Proxy',
		    306 => '(Unused)',
		    307 => 'Temporary Redirect',
		    400 => 'Bad Request',
		    401 => 'Unauthorized',
		    402 => 'Payment Required',
		    403 => 'Forbidden',
		    404 => 'Not Found',
		    405 => 'Method Not Allowed',
		    406 => 'Not Acceptable',
		    407 => 'Proxy Authentication Required',
		    408 => 'Request Timeout',
		    409 => 'Conflict',
		    410 => 'Gone',
		    411 => 'Length Required',
		    412 => 'Precondition Failed',
		    413 => 'Request Entity Too Large',
		    414 => 'Request-URI Too Long',
		    415 => 'Unsupported Media Type',
		    416 => 'Requested Range Not Satisfiable',
		    417 => 'Expectation Failed',
		    500 => 'Internal Server Error',
		    501 => 'Not Implemented',
		    502 => 'Bad Gateway',
		    503 => 'Service Unavailable',
		    504 => 'Gateway Timeout',
		    505 => 'HTTP Version Not Supported'
		);

		return (isset($codes[$status])) ? $codes[$status] : '';
	}



	// function defination to convert array to xml
	public function array_to_xml($info, &$xml_info) {
	    foreach($info as $key => $value) {
	        if(is_array($value)) {
	            if(!is_numeric($key)){
	                $subnode = $xml_info->addChild("$key");
	                $this->array_to_xml($value, $subnode);
	            }
	            else{
	                 $this->array_to_xml($value, $xml_info);
	            }
	        }
	        else {
	            $xml_info->addChild("$key","$value");
	        }
	    }
	}
	
	
	public function genRandomString($length) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $string = "";    
	    for ($p = 0; $p < $length; $p++) {
	        $string .= $characters[mt_rand(0, strlen($characters))];
	    }
	    return $string;
	}
	
	private function getTime() 
    {
    	/* 
	  $mtime = microtime(); 
	   $mtime = explode(" ",$mtime); 
	   $mtime = $mtime[1] + $mtime[0]; 
	    return $mtime; 
		 * 
		 */
		return microtime(true);
		
    } 


}
?>