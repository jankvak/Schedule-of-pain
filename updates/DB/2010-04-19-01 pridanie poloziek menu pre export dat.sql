UPDATE menu SET poradie=poradie+1 WHERE poradie<17 AND poradie>10;
UPDATE menu SET poradie=poradie+2 WHERE poradie>16;
INSERT INTO menu(name, href, group_id, poradie) VALUES('Exportovať požiadavky', 'garant/export/index', 3, 11);
INSERT INTO menu(name, href, group_id, poradie) VALUES('Exportovať požiadavky', 'scheduler/exportall/index', 1, 18);