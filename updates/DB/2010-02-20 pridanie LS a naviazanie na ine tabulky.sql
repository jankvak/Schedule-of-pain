-- Feature #13 naviazat poziadavky na semester

-- Vlozenie letneho semestra 2009/20010
INSERT INTO  semester (rok,semester,zac_uc,kon_uc,zac_skus,zac_opr,kon_skus) values (2009,2,'2010-02-15','2010-05-12','2010-05-14','2010-06-14','2010-07-02');

-- Pridanie stlca
ALTER TABLE poziadavka ADD COLUMN id_semester integer;
ALTER TABLE predmet ADD COLUMN id_semester integer;
ALTER TABLE vyucuje_predmet ADD COLUMN id_semester integer;

-- Vyplnenie stlpcov
UPDATE poziadavka SET id_semester = (SELECT id FROM semester WHERE (rok=2009 AND semester=2 AND zac_uc='2010-02-15' AND kon_uc='2010-05-12' AND zac_skus='2010-05-14' AND zac_opr='2010-06-14'AND kon_skus='2010-07-02') LIMIT 1);
UPDATE predmet SET id_semester = (SELECT id FROM semester WHERE (rok=2009 AND semester=2 AND zac_uc='2010-02-15' AND kon_uc='2010-05-12' AND zac_skus='2010-05-14' AND zac_opr='2010-06-14'AND kon_skus='2010-07-02') LIMIT 1);
UPDATE vyucuje_predmet SET id_semester = (SELECT id FROM semester WHERE (rok=2009 AND semester=2 AND zac_uc='2010-02-15' AND kon_uc='2010-05-12' AND zac_skus='2010-05-14' AND zac_opr='2010-06-14'AND kon_skus='2010-07-02') LIMIT 1);

-- Nastavenie PrimaryKey na semester id
ALTER TABLE semester ADD PRIMARY KEY (id);

-- Nastavenie FK constraints
ALTER TABLE poziadavka ADD CONSTRAINT poziadavka_id_semester_fkey FOREIGN KEY (id_semester) REFERENCES semester (id) ON UPDATE CASCADE ON DELETE CASCADE ;
ALTER TABLE poziadavka ALTER COLUMN id_semester SET NOT NULL;
ALTER TABLE predmet ADD CONSTRAINT predmet_id_semester_fkey FOREIGN KEY (id_semester) REFERENCES semester (id) ON UPDATE CASCADE ON DELETE CASCADE ;
ALTER TABLE predmet ALTER COLUMN id_semester SET NOT NULL;
ALTER TABLE vyucuje_predmet ADD CONSTRAINT vyucuje_predmet_id_semester_fkey FOREIGN KEY (id_semester) REFERENCES semester (id) ON UPDATE CASCADE ON DELETE CASCADE ;
ALTER TABLE vyucuje_predmet ALTER COLUMN id_semester SET NOT NULL;

-- Vymazanie tabulky akademicky_rok
DROP TABLE akademicky_rok;
