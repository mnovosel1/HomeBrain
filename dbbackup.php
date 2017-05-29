#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$path = dirname(__FILE__);
$configglobss = parse_ini_file($path .'/configglobs.ini');


// HBRAIN //////////////////////////////////////////////////////////////////////////////////
$sql = "
    BEGIN TRANSACTION;

        CREATE TABLE states (
            name varchar(75) NOT NULL,
            active int(1) NOT NULL DEFAULT 0
        );

";

$output = '';
exec('sqlite3 '. $path .'/var/hbrain.db \'.dump "states"\' | grep \'^INSERT\'', $output);
foreach ( $output as $line )
  $sql .= "        ".$line . "\n";

$sql .= "
        CREATE TABLE changelog (
            timestamp DATETIME,
            statebefore varchar(30) NOT NULL,
            stateid int(11) NOT NULL,
            changedto int(1) NOT NULL,
            weight int(11) NOT NULL DEFAULT 1,
            PRIMARY KEY(statebefore, stateid, changedto),
            FOREIGN KEY (stateid) REFERENCES states(rowid)
        );
";

$sql .= "
        CREATE TRIGGER changelog_trigg
            BEFORE UPDATE ON states
            FOR EACH ROW
            WHEN OLD.active <> NEW.active
            BEGIN
                INSERT OR REPLACE INTO changelog (timestamp, statebefore, stateid, changedto, weight)
                VALUES (  datetime('now','localtime'), 
                            (SELECT group_concat(active, '') FROM states ORDER BY rowid ASC), 
                            NEW.rowid, 
                            NEW.active, 
                            (SELECT weight+1 FROM changelog WHERE stateid=NEW.rowid AND changedto=NEW.active)
                        );
                DELETE FROM changelog WHERE timestamp <= date('now', '-30 day');
            END;

";

$output = '';
exec('sqlite3 '. $path .'/var/hbrain.db \'.dump "changelog"\' | grep \'^INSERT\'', $output);
foreach ( $output as $line )
  $sql .= "        ".$line . "\n";

$sql .= '    COMMIT;
';

file_put_contents($path .'/hbrain.sql', $sql);
/////////////////////////////////////////////////////////////////////////////////////////////