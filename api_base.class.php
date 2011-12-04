<?php
class API_BASE {
	
	privatE $method;//function to be called
	private $request_data_method;//required format [$_POST vs $_GET]
	private $data;//param data
	private $response_format;//response format
	
	
	public function __construct(){
		
		//connect to database
		mysql_connect("localhost", "username", "password") or $this->respond(503 ,"Database connection broken");
		mysql_select_db("db_name")or $this->respond(503, "Database connection broken");			
		
		
		//setup method and data
		$request_data_method = strtolower($_SERVER['REQUEST_METHOD']);  
		
		if($request_data_method == 'post'){
			$this->request_data_method = 'post';
			$this->data = $_POST;
		}else{
			$this->request_data_method = 'get';
			$this->data = $_GET;			
		}

		//set response format, fails to json is nothing
		$this->response_format = ($this->data["format"] == 'xml' ? 'xml' : 'json');

		
		if(empty($this->data["method"])){//in the client sence, method is the function they want to call
			$this->respond(404, 'Method was not declcared');
		}else{
			$this->method = strtolower($this->data["method"]);//define the method called in lowercase form
		}

	}
	
	public function __destruct(){
		mysql_close();
	}
	
	
	public function query($query){
		$result = mysql_query($query) or $this->respond(503, 'Database error');
		return $result;
	}
	
	public function clean($var){
		return mysql_real_escape_string($var);
	}
	
	public function getParam($param){
		return $this->data[$param];
	}
	
	public function getData(){
		return $this->data;
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
	
	public static function determineRequestMethod($load = true){
		
		$request_data_method = strtolower($_SERVER['REQUEST_METHOD']);  
		
		if($request_data_method == 'post'){
			$require =  'post';
		}else{
			$require = 'get';		
		}	
		
		if($load){
			require($request_data_method.'.class.php');
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
				array_push($return_data, $param_value);
			}
			return $return_data;
		}else{//single param
			$this->requireParam($param);
			return $param;
		}
	}
	
	//return a particular param
	private function requireParam($param){
		if(!isset($this->data[$param]) or empty($this->data[$param])){//check to ensure param exists
			$this->respond(400, "Parameter of '".$param."' is missing");
		}
		return;
	}
	
	
	
	//respond with the appropriate data
	public function respond($status = 200, $body){

		if($status != 200){
			$response = array("error" => 1, "message" => $body);
		}else{
			$response = array("error" => 0, "data" => $body);
		}
		
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

}
?>