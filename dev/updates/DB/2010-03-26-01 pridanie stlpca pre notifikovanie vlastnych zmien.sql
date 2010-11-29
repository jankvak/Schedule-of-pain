-- Pridanie stlpca
ALTER TABLE pedagog ADD COLUMN posielat_moje_zmeny boolean;

-- Nastavenie default hodnoty
ALTER TABLE pedagog ALTER COLUMN posielat_moje_zmeny SET DEFAULT FALSE;

-- Nastavenie vsetkym pedagogom, ktory maju posielat_moje_zmeny NULL na default false
UPDATE pedagog SET posielat_moje_zmeny = FALSE WHERE posielat_moje_zmeny IS NULL;

-- Nastavenie komentara
COMMENT ON COLUMN pedagog.posielat_moje_zmeny IS 'Informacia o tom, ci ma byt dana osoba notifikovana mailom v pripade ak vykona urcitu akciu.';

-- Nastavenie NOT NULL constraint, neviem, ci to bude potrebne
--ALTER TABLE pedagog ALTER COLUMN posielat_moje_zmeny SET NOT NULL;