
-- SQL migration from version 2016.01.01
-- Use the upgrade script upgrade.php!

ALTER TABLE :Course MODIFY COLUMN Name VARCHAR(80) NOT NULL;
ALTER TABLE :Course MODIFY COLUMN Link VARCHAR(256) NOT NULL;
ALTER TABLE :Course MODIFY COLUMN Map VARCHAR(256) NOT NULL;
