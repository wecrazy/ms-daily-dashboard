-- MS Daily Dashboard - Default seed data
-- Auto-run by bin/db-setup.php when tables are empty

-- Default users (password = "admin" hashed with SHA-256)
INSERT IGNORE INTO `user` (`ID`, `UserName`, `Password`) VALUES
(1, 'admin', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918'),
(2, 'user',  '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918');
