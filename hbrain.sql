CREATE TABLE states (
  name varchar(75) NOT NULL,
  active int(1) NOT NULL DEFAULT 0
);

CREATE TABLE changelog (
  timestamp DATETIME DEFAULT (datetime('now','localtime')),
  statebefore varchar(30) NOT NULL,
  stateid int(11) NOT NULL,
  changedto int(1) NOT NULL,
  FOREIGN KEY (stateid) REFERENCES states(rowid)
);

CREATE TRIGGER changelog_trigg
  BEFORE UPDATE
  ON states
  FOR EACH ROW
  WHEN OLD.active <> NEW.active
BEGIN
  INSERT INTO changelog (statebefore, stateid, changedto)
  VALUES ((SELECT group_concat(active, '') FROM states), NEW.rowid, NEW.active);
  DELETE FROM changelog WHERE timestamp <= date('now', '-30 day');
END;


INSERT INTO states (name) VALUES
('KODI'),
('HomeServer'),
('HomeBrain user'),
('HomeServer user'),
('TV recording'),
('Torrenting');