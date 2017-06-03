
    BEGIN TRANSACTION;

        CREATE TABLE states (
            name varchar(75) NOT NULL,
            auto int(1) NOT NULL DEFAULT 1,
            active int(1) NOT NULL DEFAULT 0
        );

        INSERT INTO "states" VALUES('KODI',1,0);
        INSERT INTO "states" VALUES('HomeServer',1,0);
        INSERT INTO "states" VALUES('HomeBrain user',0,0);
        INSERT INTO "states" VALUES('HomeServer user',0,0);
        INSERT INTO "states" VALUES('TV recording',0,0);
        INSERT INTO "states" VALUES('Torrenting',0,1);
        INSERT INTO "states" VALUES('MPD playing',1,0);
        INSERT INTO "states" VALUES('Heating',1,0);
        INSERT INTO "states" VALUES('KODI',1,0);
        INSERT INTO "states" VALUES('HomeServer',1,0);
        INSERT INTO "states" VALUES('HomeBrain user',0,0);
        INSERT INTO "states" VALUES('HomeServer user',0,0);
        INSERT INTO "states" VALUES('TV recording',0,0);
        INSERT INTO "states" VALUES('Torrenting',0,1);
        INSERT INTO "states" VALUES('MPD playing',1,0);
        INSERT INTO "states" VALUES('Heating',1,0);

        CREATE TABLE changelog (
            timestamp DATETIME,
            statebefore varchar(30) NOT NULL,
            stateid int(11) NOT NULL,
            changedto int(1) NOT NULL,
            PRIMARY KEY(statebefore, stateid, changedto),
            FOREIGN KEY (stateid) REFERENCES states(rowid)
        );

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
                DELETE FROM changelog WHERE timestamp <= date('now', '-60 day');
            END;


        CREATE VIEW logic AS 
            SELECT 
                    COUNT(*) AS weight, 
                    STRFTIME('%H', timestamp)*1 AS hour,
                    STRFTIME('%w', timestamp)*1 AS dow,
                    c.statebefore, 
                    c.changedto, 
                    s.name, 
                    s.auto
                FROM changelog c join states s ON c.stateid=s.rowid
                GROUP BY c.statebefore, c.stateid, c.changedto
                ORDER BY weight desc, c.timestamp desc;


    COMMIT;

