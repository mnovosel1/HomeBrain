#!/usr/bin/php
<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);

$path = str_replace('/heating', '', dirname(__FILE__));
$configs = parse_ini_file($path .'/heating/config.ini');

$db         = new SQLite3($path .'/var/heating.db');

$tempSet = $db->querySingle("SELECT temp 
                              FROM tempConf 
                              WHERE wday = STRFTIME('%w', DATETIME('now', 'localtime'))
                               AND hour = STRFTIME('%H', DATETIME('now', 'localtime'));");

switch (date("n")) {
    case 3:
    case 4:
    case 5:
    case 10:
        $tempSet -= 5;
        break;
    case 6:
    case 7:
    case 8:
    case 9:
        $tempSet = $configs["TEMPSET_MIN"];
        break;
}

$tempHumidIn = explode('|', exec($path ."/heating/DHT.py"));
$tempIn = $tempHumidIn[0];
$humidIn = round($tempHumidIn[1], 0);

$temp = $db->querySingle( "SELECT (timestamp < datetime(datetime('now', 'localtime'), '-300 seconds')) as old, *
                            FROM tempLog
                             ORDER BY timestamp DESC
                              LIMIT 1;", TRUE);

$oldLog     = $temp['old'];
$tempOut    = $temp['tempOut'];
$heatingOn  = $temp['heatingOn'];


if ( true || date("i")%5 == 0 ) {
  //$tempOut = round(exec($path ."/heating/getOutTemp.py"), 0);
  
  /*
  $tempOut = explode(':', exec('curl -s -X GET http://api.wunderground.com/api/65bdc72ba6b054fd/geolookup/conditions/q/Europe/Samobor.json | sed \'s/\\\\\//\//g\' | sed \'s/[{}]//g\' | awk -v k="text" \'{n=split($0,a,","); for (i=1; i<=n; i++) print a[i]}\' | sed \'s/\"\:\"/\|/g\' | sed \'s/[\,]/ /g\' | sed \'s/\"//g\' | grep -w temp_c'));
  $tempOut = $tempOut[1];
  */

  $tempOut = exec('/usr/bin/php '.$path.'/heating/getOutTemp.php');
}

if ( $tempOut > 16 ) $tempSet = 0;

echo $oldLog ."|". $tempSet ."|". $tempIn ."|". $tempOut ."|". $heatingOn ."|". $humidIn;
?>