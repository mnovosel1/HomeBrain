<?php

	function fcm($verb)
	{
		$sqlite = new SQLite3(DIR .'/var/hbrain.db');
		
		$args   = func_get_args();

		switch ($verb)
		{
			case 'reg':
				$ret = json_encode($sqlite->query("INSERT INTO fcm VALUES(datetime('now', 'localtime'), '".$args[1][0][0]."')"));
				exec ("cp ". DIR ."/var/hbrain.db ". DIR ."/var_sav/hbrain.db");
				return $ret;
			break;
		}
			
		return FALSE;
	}

?>