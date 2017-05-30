#!/usr/bin/php
<?php

$sqlitedb = new SQLite3($path .'/var/hbrain.db');
$sqliteres = $sqlitedb->query("SELECT group_concat(active, '') FROM states ORDER BY rowid ASC");

var_dump($sqliteres->fetchArray(SQLITE3_ASSOC));

?>