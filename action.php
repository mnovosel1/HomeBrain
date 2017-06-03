#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$path = dirname(__FILE__);
$ret = array();

$sqlitedb = new SQLite3($path .'/var/hbrain.db');


$sqliteres = $sqlitedb->query("SELECT COUNT(*) FROM logic 
                                WHERE auto=1
                                 AND hour=STRFTIME('%H', 'now')
                                 AND statebefore=(SELECT group_concat(active, '') FROM states ORDER BY rowid ASC)"
                            );
$num = $sqliteres->fetchArray();

if ( $num[0] > 0 )
{
    $sqliteres = $sqlitedb->query("SELECT name FROM logic
                                    WHERE auto=1
                                     AND hour=STRFTIME('%H', 'now')
                                     AND statebefore=(SELECT group_concat(active, '') FROM states ORDER BY rowid ASC)"
                                );
    while ($entry = $sqliteres->fetchArray(SQLITE3_ASSOC))
    {
        $ret[] = $entry['name'];
    }
}

echo json_encode($ret);
?>