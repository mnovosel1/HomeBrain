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

echo "Now I'm: |";
for ($i=1; $i <= strlen($status); $i++)
{ 
    if ( $status[$i-1] == 1 )
    {
        echo $states[$i];
        echo "|";
    }
}
echo PHP_EOL;

$sqliteres = $sqlitedb->query("SELECT COUNT(*) FROM logic 
                                WHERE auto=1
                                 AND hour=STRFTIME('%H', 'now')
                                 AND wday=STRFTIME('%w', 'now')
                                 AND statebefore=(SELECT group_concat(active, '') FROM states ORDER BY rowid ASC)"
                            );
$num = $sqliteres->fetchArray();

if ( $num[0] > 0 )
{
    echo "should I: |";
    $sqliteres = $sqlitedb->query("SELECT name FROM logic
                                    WHERE auto=1
                                     AND hour=STRFTIME('%H', 'now')*1
                                     AND wday=STRFTIME('%w', 'now')*1
                                     AND statebefore=(SELECT group_concat(active, '') FROM states ORDER BY rowid ASC)"
                                );
    while ($entry = $sqliteres->fetchArray(SQLITE3_ASSOC))
    {
        echo $entry['name'];
        echo "|";
    }
    echo PHP_EOL;
}
?>