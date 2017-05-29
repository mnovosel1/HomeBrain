
    BEGIN TRANSACTION;

        CREATE TABLE states (
            name varchar(75) NOT NULL,
            auto int(1) NOT NULL DEFAULT 0,
            active int(1) NOT NULL DEFAULT 0
        );

        INSERT INTO "states" VALUES('KODI',1,0);
        INSERT INTO "states" VALUES('HomeServer',1,0);
        INSERT INTO "states" VALUES('HomeBrain user',0,0);
        INSERT INTO "states" VALUES('HomeServer user',0,0);
        INSERT INTO "states" VALUES('TV recording',0,0);
        INSERT INTO "states" VALUES('Torrenting',0,0);

        CREATE TABLE changelog (
            timestamp DATETIME,
            statebefore varchar(30) NOT NULL,
            stateid int(11) NOT NULL,
            changedto int(1) NOT NULL,
            weight int(11) NOT NULL DEFAULT 1,
            PRIMARY KEY(statebefore, stateid, changedto),
            FOREIGN KEY (stateid) REFERENCES states(rowid)
        );

        CREATE TRIGGER changelog_trigg
            BEFORE UPDATE ON states
            FOR EACH ROW
            WHEN OLD.active <> NEW.active
            BEGIN
                INSERT OR REPLACE INTO changelog (timestamp, statebefore, stateid, changedto, weight)
                VALUES (
                            datetime('now','localtime'), 
                            (SELECT group_concat(active, '') FROM states ORDER BY rowid ASC), 
                            NEW.rowid, 
                            NEW.active, 
                            (SELECT weight+1 FROM changelog WHERE statebefore=(SELECT group_concat(active, '') FROM states ORDER BY rowid ASC) AND stateid=NEW.rowid AND changedto=NEW.active)
                        );
                DELETE FROM changelog WHERE timestamp <= date('now', '-30 day');
            END;


        CREATE VIEW logic AS 
            SELECT c.weight, c.statebefore, c.changedto, s.name
                FROM changelog c join states s ON c.stateid=s.rowid
                ORDER BY c.weight desc, c.timestamp desc;


    COMMIT;

