/*pridany cudzi kluc do tabulky student*/

ALTER TABLE student ADD CONSTRAINT student_id_studijny_program_fkey FOREIGN KEY (id_studijny_program) REFERENCES studijny_program (id) ON UPDATE CASCADE ON DELETE CASCADE ;

/*odstranenie stlpca studijny program*/
ALTER TABLE student DROP COLUMN studijny_program