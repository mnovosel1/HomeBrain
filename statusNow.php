#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$path = dirname(__FILE__);

$sqlitedb = new SQLite3($path .'/var/hbrain.db');
$sqliteres = $sqlitedb->query("SELECT group_concat(active, '') status FROM states ORDER BY rowid ASC");

$status = $sqliteres->fetchArray(SQLITE3_ASSOC);
$status = $status['status'];


$sqliteres = $sqlitedb->query("SELECT rowid, * FROM states ORDER BY rowid ASC");
while ($entry = $sqliteres->fetchArray(SQLITE3_ASSOC))
{
    $states[$entry['rowid']] = $entry['name'];
}

echo "now: |";
for ($i=1; $i <= strlen($status); $i++)
{ 
    if ( $status[$i-1] == 1 )
    {
        echo $states[$i];
        echo "|";
    }
}
echo PHP_EOL;


$sqliteres = $sqlitedb->query("SELECT * FROM logic WHERE statebefore=(SELECT group_concat(active, '') FROM states ORDER BY rowid ASC)");
var_dump($sqliteres->numColumns());
//echo "should I: |"
?>