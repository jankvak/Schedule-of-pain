-- odstranenie prebytocnych referencii, 
-- normalne su dostupne tranzitivne cez tabulku predmet

ALTER TABLE vyucuje_predmet DROP COLUMN id_semester;
ALTER TABLE meta_poziadavka DROP COLUMN id_semester;

-- naviazanie osobnych priorit na semester

ALTER TABLE priorita_vyucby ADD COLUMN id_semester INTEGER;
UPDATE priorita_vyucby SET id_semester=(SELECT id FROM semester WHERE rok=2009 AND semester=2);
ALTER TABLE priorita_vyucby ADD CONSTRAINT priorita_vyucby_id_semester_fkey FOREIGN KEY(id_semester) REFERENCES semester(id) ON UPDATE CASCADE ON DELETE CASCADE;