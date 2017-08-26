#!/usr/bin/php
<?php
//error_reporting(E_ERROR | E_WARNING | E_PARSE);

define('DIR', str_replace('/lan', '', dirname(__FILE__)));

$configs = parse_ini_file(DIR .'/config.ini');

$db      = new SQLite3(DIR .'/var/hbrain.db');

$serverlive = exec("ping -c1 10.10.10.100 | grep 'received' | awk -F ',' '{print $2}' | awk '{ print $1}'");
$db->query("UPDATE states SET active=".$serverlive." WHERE name='HomeServer'");

if ( $serverlive > 0 )
{
	$srvWakeTime = exec('/usr/bin/ssh server@10.10.10.100 "/home/server/chkServer"');
				   exec('echo '.$srvWakeTime.' > '. DIR .'/var/srvWakeTime.log');
}
else
	$srvWakeTime = exec('cat '.DIR.'/var/srvWakeTime.log');

$kodilive = exec("ping -c1 10.10.10.10 | grep 'received' | awk -F ',' '{print $2}' | awk '{ print $1}'");
$db->query("UPDATE states SET active=".$kodilive." WHERE name='KODI'");

$hbrainuser = exec("who | wc -l");
$hbrainuser = ($hbrainuser > 0) ? 1 : 0;
$db->query("UPDATE states SET active=".$hbrainuser." WHERE name='HomeBrain user'");

$mpdplay = exec("mpc status | grep playing");
$mpdplay = ($mpdplay == "") ? 0 : 1;
$db->query("UPDATE states SET active=".$mpdplay." WHERE name='MPD playing'");


$sql = "SELECT name, active FROM states;";
$result = $db->query($sql);
while ($row = $result->fetchArray(SQLITE3_ASSOC))
{
  $table[$row['name']] = $row['active'];
}

// server je ugašen
if ( $table["HomeServer"] < 1 )
{
	// budi server ako ..
	switch (true)
	{
		case ( ($srvWakeTime - time()) <= 1800 ): // .. je srvWakeTime za pola sata ili manje
		case ( $table["KODI"] > 0 ): // .. je KODI upaljen
			exec(DIR . "/lan/srvWake.sh;");
		
		default:
			break;
	}
}
// server je upaljen
else
{
	// ne gasi server ako ..
	switch (true)
	{
		case ( $table["KODI"] > 0 ): // .. je Kodi upaljen
		case ( $table["HomeServer busy"] > 0 ): // .. je HomeServer busy
		case ( $table["HomeBrain user"] > 0 ): // .. HomeBrain ima usera
			break;
		
		default:
			exec(DIR . "/lan/srvShut.sh;");
		
	}
}

?>