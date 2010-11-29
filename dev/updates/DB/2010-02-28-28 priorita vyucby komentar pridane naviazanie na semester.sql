ALTER TABLE priorita_komentar ADD COLUMN id_semester INTEGER;
UPDATE priorita_komentar SET id_semester=(SELECT id FROM semester WHERE rok=2009 AND semester=2);
ALTER TABLE priorita_komentar ALTER COLUMN id_semester SET NOT NULL;
ALTER TABLE priorita_komentar ADD CONSTRAINT priorita_komentar_id_semester_fkey FOREIGN KEY(id_semester) REFERENCES semester(id) ON UPDATE CASCADE ON DELETE CASCADE;
