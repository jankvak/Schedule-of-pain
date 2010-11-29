-- predmet
ALTER TABLE zapisany_predmet DROP CONSTRAINT zapisany_predmet_id_predmet_fkey;
ALTER TABLE zapisany_predmet ADD CONSTRAINT zapisany_predmet_id_predmet_fkey
FOREIGN KEY (id_predmet) REFERENCES predmet(id) ON UPDATE CASCADE ON DELETE CASCADE;
-- student
ALTER TABLE zapisany_predmet DROP CONSTRAINT zapisany_predmet_id_student_fkey;
ALTER TABLE zapisany_predmet ADD CONSTRAINT zapisany_predmet_id_student_fkey
FOREIGN KEY (id_student) REFERENCES student(id) ON UPDATE CASCADE ON DELETE CASCADE;



