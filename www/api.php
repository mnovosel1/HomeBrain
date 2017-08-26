<?php

/* WORKING DIR constant */
define('DIR', str_replace('/www', '', dirname(__FILE__)));

/* CLASS definition */
require_once (DIR . '/www/class.api.php');

/* USER CLASS definition */
class MyAPI extends API
{
    protected $User, $path, $sqlite;

    public function __construct($request, $origin) {
		$this->path 	= DIR;		
        parent::__construct($request);
    }

    /* Example of an Endpoint */
     protected function example() {
        if ($this->method == 'GET')
		{
			print_r($this->args);
			var_dump($_SERVER);
            return "Your name is " . $this->verb;
        } else {
            return "Only accepts GET requests";
        }
     }
	 
	 
    /* HomeServer Endpoint */
	 protected function hsrv() {
		 if ( strpos($_SERVER["HTTP_USER_AGENT"], 'homeserver/') !== false && $this->method == 'PUT' )
		 {
			$this->sqlite 	= new SQLite3(DIR .'/var/hbrain.db');
			$this->sqlite->query("UPDATE states SET active=1 WHERE name='HomeServer'");
			
			switch ($this->verb)
			{
				case 'serverbusy':
					return $this->sqlite->query("UPDATE states SET active=".$this->args[0]." WHERE name='HomeServer busy'");
				break;
			}
		 }
		 
		 return false;		 
	 }
 }

 
 
 /* Requests from the same server don't have a HTTP_ORIGIN header */
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {
    $API = new MyAPI($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
    echo $API->processAPI();
} catch (Exception $e) {
    echo json_encode(Array('error' => $e->getMessage()));
}

?>