-- naviazanie na Ing PKSS
UPDATE student SET id_studijny_program=5 WHERE id_studijny_program IS NULL;
-- korektna schema
ALTER TABLE student ALTER COLUMN id_studijny_program SET NOT NULL;