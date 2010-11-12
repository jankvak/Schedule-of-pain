ALTER TABLE rozvrhova_akcia DROP CONSTRAINT rozvrhova_akcia_id_semester_fkey;
ALTER TABLE rozvrhova_akcia ADD CONSTRAINT rozvrhova_akcia_id_semester_fkey
FOREIGN KEY (id_semester) REFERENCES semester(id) ON UPDATE CASCADE ON DELETE CASCADE;

