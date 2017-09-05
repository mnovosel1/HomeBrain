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
	 
	 protected function fcm()
	 {
		if ( $this->method == 'PUT' )
		{
			$this->sqlite 	= new SQLite3(DIR .'/var/fcm.db');

			switch ($this->verb)
			{
				case 'reg':
					return $this->sqlite->query("INSERT INTO tokens VALUES(datetime('now', 'localtime'), '".$this->args[0]."')");
				break;
			}			
		}
	 }
	 
    /* HomeServer Endpoint */
	 protected function hsrv()
	 {
		if ( $this->method == 'PUT' )
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
		 
		 return FALSE;		 
	 }
	 
	 protected function html()
	 {
		 phpinfo();
	 }
 }

 
 
 /* Requests from the same server don't have a HTTP_ORIGIN header */
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

if ( strpos($_SERVER["HTTP_USER_AGENT"],  'homeserver/') !== false || strpos($_SERVER["HTTP_USER_AGENT"],  'homebrainapp/') !== false || strpos($_SERVER["HTTP_USER_AGENT"],  'homebrainweb/') !== false )
{
	try {
	    $API = new MyAPI($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
	    echo $API->processAPI();
	} catch (Exception $e) {
	    echo json_encode(Array('error' => $e->getMessage()));
	}
}

?>