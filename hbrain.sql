
    BEGIN TRANSACTION;

        CREATE TABLE states (
            name varchar(75) NOT NULL,
            auto int(1) NOT NULL DEFAULT 1,
            active int(1) NOT NULL DEFAULT 0
        );

        INSERT INTO "states" VALUES('KODI',1,0);
        INSERT INTO "states" VALUES('HomeServer',1,0);
        INSERT INTO "states" VALUES('HomeServer busy',0,0);
        INSERT INTO "states" VALUES('HomeBrain user',0,1);
        INSERT INTO "states" VALUES('MPD playing',1,0);
        INSERT INTO "states" VALUES('Heating',1,0);

 
		CREATE TABLE fcm (
					timestamp DATETIME,
					token varchar(99) NOT NULL,
					PRIMARY KEY(token)
				);

        INSERT INTO "fcm" VALUES('2017-09-11 10:44:59','ezLt5t8albg:APA91bF4M3TtrD9GsiSU4flu0fh5owQ1cjhs6QipFezzTikcciHXcfcVU9junXiJGpeoqcjjumHGvNXOLVjjqxtGS_ms4m_mEo3w6_UBbUbHu0yXvPlyKk7Gx6YIJKsYtWcZdmlt8O3R');
        INSERT INTO "fcm" VALUES('2017-09-11 10:46:41','fNbA8yNMjec:APA91bHeXEju30EbN6FMn2dBCCsohIheVhEjsWSzFNjO3QvcA8_ZH8crfV2PhGJ9TqR8MjC_DADL24xAhDrwtQfpYRH2HQAkEYTmgKYlW50Sgu8veKDJNkyQ-ZFEZcTMiS8UEDSNtvTu');

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

