-- Bug #22 naviazat poziadavky na semester

-- Vlozenie noveho stlpca
ALTER TABLE student ADD id_studijny_program integer NULL;

-- Vyplnenie stlpcov
UPDATE student SET id_studijny_program='1' WHERE studijny_program='B-INFO';
UPDATE student SET id_studijny_program='2' WHERE studijny_program='B-PSS';
UPDATE student SET id_studijny_program='4' WHERE studijny_program='I-IS2';
UPDATE student SET id_studijny_program='4' WHERE studijny_program='I-IS3';
UPDATE student SET id_studijny_program='3' WHERE studijny_program='I-SI2';
UPDATE student SET id_studijny_program='3' WHERE studijny_program='I-SI3';
UPDATE student SET id_studijny_program='5' WHERE studijny_program='I-PSS2';
UPDATE student SET id_studijny_program='5' WHERE studijny_program='I-PSS3';
