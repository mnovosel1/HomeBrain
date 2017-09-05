#!/usr/bin/php
<?php
//error_reporting(E_ERROR | E_WARNING | E_PARSE);

define('DIR', str_replace('/lan', '', dirname(__FILE__)));

$configs = parse_ini_file(DIR .'/config.ini');

$db      = new SQLite3(DIR .'/var/hbrain.db');

$sql = "SELECT name, active FROM states;";
$result = $db->query($sql);
while ($row = $result->fetchArray(SQLITE3_ASSOC))
{
  $table[$row['name']] = $row['active'];
}

// funkcija koja šalje notifikacije //////////////
function notify ($msg)
{
	exec(DIR . '/notify/fcm.py "' . $msg . '"');
	exec(DIR . '/notify/kodi.php "' . $msg . '"');
}
//////////////////////////////////////////////////

// HomeServer /////////////////////////////////////////////////////////////////////////////////////////
$serverlive = exec("ping -c1 10.10.10.100 | grep 'received' | awk -F ',' '{print $2}' | awk '{ print $1}'");
$db->query("UPDATE states SET active=".$serverlive." WHERE name='HomeServer'");

if ( $serverlive != $table["HomeServer"] )
{
	$status = ($serverlive > 0) ? 'upaljen' : 'ugašen';
	notify('HomeServer je ' . $status . '.');
}

if ( $serverlive > 0 )
{
	$srvWakeTime = exec('/usr/bin/ssh server@10.10.10.100 "/home/server/chkServer"');
	exec('echo '.$srvWakeTime.' > '. DIR .'/var/srvWakeTime.log');
}
else
{
	$db->query("UPDATE states SET active=0 WHERE name='HomeServer busy'");
	$srvWakeTime = exec('cat '.DIR.'/var/srvWakeTime.log');
}
///////////////////////////////////////////////////////////////////////////////////////////////////////

// KODI ///////////////////////////////////////////////////////////////////////////////////////////////
$kodilive = exec("ping -c1 10.10.10.20 | grep 'received' | awk -F ',' '{print $2}' | awk '{ print $1}'");
$db->query("UPDATE states SET active=".$kodilive." WHERE name='KODI'");
if ( $kodilive != $table["KODI"] )
{
	$status = ($kodilive > 0) ? 'upaljen' : 'ugašen';
	notify('KODI je ' . $status . '.');
///////////////////////////////////////////////////////////////////////////////////////////////////////
}

// HomeBrain user /////////////////////////////////////////////////////////////////////////////////////
$hbrainuser = exec("who | wc -l");
$hbrainuser = ($hbrainuser > 0) ? 1 : 0;
$db->query("UPDATE states SET active=".$hbrainuser." WHERE name='HomeBrain user'");
if ( $hbrainuser != $table["HomeBrain user"] )
{	
	$status = ($hbrainuser > 0) ? 'prijavljen' : 'odjavljen';
	notify('HomeBrain user je ' . $status . '.');
}
///////////////////////////////////////////////////////////////////////////////////////////////////////

// MPD player /////////////////////////////////////////////////////////////////////////////////////////
$mpdplay = exec("mpc status | grep playing");
$mpdplay = ($mpdplay == "") ? 0 : 1;
$db->query("UPDATE states SET active=".$mpdplay." WHERE name='MPD playing'");
if ( $mpdplay != $table["MPD playing"] )
{	
	$status = ($mpdplay > 0) ? 'svira' : 'je ugašen';
	notify('MPD ' . $status . '.');
}
///////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////




///////////////////////////////////////////////////////////////////////////////////////////////////////
// AKCIJE /////////////////////////////////////////////////////////////////////////////////////////////
$sql = "SELECT name, active FROM states;";
$result = $db->query($sql);
while ($row = $result->fetchArray(SQLITE3_ASSOC))
  $table[$row['name']] = $row['active'];

// server je ugašen
if ( $serverlive < 1 )
{
	// budi server ako ..
	switch (true)
	{
		case ( ($srvWakeTime - time()) <= 1800 ): // .. je srvWakeTime za pola sata ili manje
		case ( $kodilive > 0 ): // .. je KODI upaljen
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
		case ( $kodilive > 0 ): // .. je Kodi upaljen
		case ( $table["HomeServer busy"] > 0 ): // .. je HomeServer busy
		case ( $hbrainuser > 0 ): // .. HomeBrain ima usera
			break;
		
		default:
			exec(DIR . "/lan/srvShut.sh;");
		
	}
}
///////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////

?>