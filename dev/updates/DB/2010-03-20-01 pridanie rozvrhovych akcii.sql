-- vytvorenie tabulky rozvrhovych akcii
CREATE TABLE rozvrhova_akcia(
	id SERIAL,
	id_semester INTEGER NOT NULL,
	zaciatok TIMESTAMP,
	koniec TIMESTAMP,
	PRIMARY KEY(id),
	FOREIGN KEY(id_semester) REFERENCES semester(id)
);
-- posunutie poloziek poradie
UPDATE menu SET poradie=poradie+1 WHERE poradie>4;
-- vlozenie polozky do menu
INSERT INTO menu(name, href, group_id, poradie) VALUES('Rozvrhové akcie', 'ape/collection/index', 6, 5);