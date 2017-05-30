#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$path = dirname(__FILE__);
$configglobss = parse_ini_file($path .'/configglobs.ini');

$sqlitedb = new SQLite3($path .'/var/hbrain.db');
$mysqlidb = new mysqli($configglobss["DB_REPLIC_HOST"], $configglobss["DB_REPLIC_USER"], $configglobss["DB_REPLIC_PASS"], $configglobss["DB_REPLIC_DBNAME"]);

$sqliteres = $sqlitedb->query('SELECT c.timestamp, c.statebefore, c.changedto, s.name state FROM changelog c JOIN states s ON c.stateid = s.rowid;');

while ($entry = $sqliteres->fetchArray(SQLITE3_ASSOC)) {
    $mysqlidb->query("INSERT INTO changeLog (timestamp, statebefore, state, changedto) 
                        VALUES (
                                '".$entry['timestamp']."',
                                '".$entry['statebefore']."',
                                '".$entry['state']."',
                                ".$entry['changedto'].",
                                )");
}
echo "INSERT INTO changeLog (timestamp, statebefore, state, changedto) 
                        VALUES (
                                '".$entry['timestamp']."',
                                '".$entry['statebefore']."',
                                '".$entry['state']."',
                                ".$entry['changedto'].",
                                )";
$sqlitedb->close();
$mysqlidb->close();

// HBRAIN //////////////////////////////////////////////////////////////////////////////////
$sql = "
    BEGIN TRANSACTION;

        CREATE TABLE states (
            name varchar(75) NOT NULL,
            auto int(1) NOT NULL DEFAULT 1,
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
                INSERT OR REPLACE INTO changelog (timestamp, statebefore, stateid, changedto)
                VALUES (
                            datetime('now','localtime'), 
                            (SELECT group_concat(active, '') FROM states ORDER BY rowid ASC), 
                            NEW.rowid, 
                            NEW.active
                        );
                DELETE FROM changelog WHERE timestamp <= date('now', '-90 day');
            END;

";

$sql .= "
        CREATE VIEW logic AS 
            SELECT COUNT(*) AS weight, c.statebefore, c.changedto, s.name
                FROM changelog c join states s ON c.stateid=s.rowid
                GROUP BY c.statebefore, c.stateid, c.changedto
                ORDER BY c.weight desc, c.timestamp desc;

";

$sql .= "
    COMMIT;

";

file_put_contents($path .'/hbrain.sql', $sql);
/////////////////////////////////////////////////////////////////////////////////////////////