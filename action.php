#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$path = dirname(__FILE__);
$ret = array();
$retBOOL = false;

$sqlitedb = new SQLite3($path .'/var/hbrain.db');

$sql = "
                                 AND dow=STRFTIME('%w', 'now')*1
                                 AND hour=STRFTIME('%H', 'now')*1 ";
$sql2 = "
                                 AND hour=STRFTIME('%H', 'now')*1 ";
$sql_end = "
                                 AND statebefore=(SELECT group_concat(active, '') FROM states ORDER BY rowid ASC)";

$sqliteres = $sqlitedb->query("SELECT COUNT(*) FROM logic 
                                WHERE auto=1 " . $sql . $sql_end
                            );
$num = $sqliteres->fetchArray();

if ( $num[0] < 1 ) $sql = $sql2;

$sqliteres = $sqlitedb->query("SELECT COUNT(*) FROM logic 
                                WHERE auto=1 " . $sql . $sql_end
                            );
$num = $sqliteres->fetchArray();

if ( $num[0] < 1 ) $sql = " ";

$sqliteres = $sqlitedb->query("SELECT COUNT(*) FROM logic 
                                WHERE auto=1 " . $sql . $sql_end
                            );
$num = $sqliteres->fetchArray();

if ( $num[0] > 0 )
{
    $sqliteres = $sqlitedb->query("SELECT name FROM logic
                                WHERE auto=1 " . $sql . $sql_end
                                );
    while ($entry = $sqliteres->fetchArray(SQLITE3_ASSOC))
    {
        $ret[] = $entry['name'];
        $retBOOL = true;
    }
}

if ( $retBOOL ) echo json_encode($ret);
?>