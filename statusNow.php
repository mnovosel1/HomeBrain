#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$path = dirname(__FILE__);

$sqlitedb = new SQLite3($path .'/var/hbrain.db');
$sqliteres = $sqlitedb->query("SELECT group_concat(active, '') status FROM states ORDER BY rowid ASC");

$status = $sqliteres->fetchArray(SQLITE3_ASSOC);
$status = $status['status'];


$sqliteres = $sqlitedb->query("SELECT * FROM states ORDER BY rowid ASC");
var_dump($sqliteres->fetchArray(SQLITE3_ASSOC));

?>