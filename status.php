#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$path = dirname(__FILE__);
$ret = array();
$retBOOL = false;

$sqlitedb = new SQLite3($path .'/var/hbrain.db');

$sqliteres = $sqlitedb->query("SELECT group_concat(active, '') status FROM states ORDER BY rowid ASC");

$status = $sqliteres->fetchArray(SQLITE3_ASSOC);
$status = $status['status'];


$sqliteres = $sqlitedb->query("SELECT rowid, * FROM states ORDER BY rowid ASC");
while ($entry = $sqliteres->fetchArray(SQLITE3_ASSOC))
{
    $states[$entry['rowid']] = $entry['name'];
}

for ($i=1; $i <= strlen($status); $i++)
{ 
    $ret[] = array($states[$i] => $status[$i-1]);
}

echo json_encode($ret);
?>