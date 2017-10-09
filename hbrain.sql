
    BEGIN TRANSACTION;

        CREATE TABLE states (
            name varchar(50) PRIMARY KEY,
            auto int(1) NOT NULL DEFAULT 1,
            active int(1) NOT NULL DEFAULT 0
        );

        INSERT INTO "states" VALUES('KODI',1,0);
        INSERT INTO "states" VALUES('HomeServer',1,0);
        INSERT INTO "states" VALUES('HomeServer busy',0,0);
        INSERT INTO "states" VALUES('HomeBrain user',0,0);
        INSERT INTO "states" VALUES('MPD playing',1,0);
        INSERT INTO "states" VALUES('Heating',1,0);
 
		CREATE TABLE fcm (
			timestamp DATETIME,
			token varchar(99) NOT NULL,
			PRIMARY KEY(token)
		);
        
        INSERT INTO "fcm" VALUES('2017-10-09 00:41:28','e7nifuUDIk0:APA91bGTmmySKdD0fZw7aO1F798pNGIZe3AXSSsYUV8-YbBUpSTUfSG-_er714tfhi8M_slVGV-sMJGeOywdwdDSsYxMHKb8W7toOzjiDlYn4zMxQ2-kG_6yXCObwLB_TtKCfqy4jTZi');
        INSERT INTO "fcm" VALUES('2017-10-09 00:42:44','eNFcmDTIMFo:APA91bFfEBXxaRG7LcRqncYIvu72oDjQKeOYK_mOlEq7xAkUFIl8joL1Tttg-pH6Q7m1YAMn1jrE57_pALEXKOfkkFxnigrwysOveGKsAhlhOemdAy6DekzIbIZjZI-FhYwRzkWaGudm');
        INSERT INTO "fcm" VALUES('2017-10-09 01:04:04','es7Oi_wjP1M:APA91bE-JUrYINLVePFwuikEQP-A0uxYXQ3kaR2X532vyQiJQuRx3gUUNmTpBLJeWKU2P33ei8kKLMwDWkcc2YJreC6XB4QjzAdhAjLpha1ZpTlNJ8nqNAY42vVDnoFco3_QB1qBwmD9');

        CREATE TABLE changelog (
            timestamp DATETIME,
            statebefore varchar(30) NOT NULL,
            state varchar(50) NOT NULL,
            changedto int(1) NOT NULL,
            PRIMARY KEY(statebefore, state, changedto),
            FOREIGN KEY (state) REFERENCES states(name)
        );

        CREATE TRIGGER changelog_trigg
            BEFORE UPDATE ON states
            FOR EACH ROW
            WHEN OLD.active <> NEW.active
            BEGIN
                INSERT OR REPLACE INTO changelog (timestamp, statebefore, state, changedto)
                VALUES (
                            datetime('now','localtime'), 
                            (SELECT group_concat(active, '') FROM states), 
                            NEW.name, 
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
                FROM changelog c join states s ON c.state=s.name
                GROUP BY c.statebefore, c.state, c.changedto
                ORDER BY weight desc, c.timestamp desc;

    COMMIT;

