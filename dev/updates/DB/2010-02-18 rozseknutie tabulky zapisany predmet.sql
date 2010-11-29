-- Feature #23

ALTER TABLE zapisany_predmet DROP id_vyucovacia_hodina CASCADE;

CREATE TABLE zapisany_predmet_hodina 
(
      id SERIAL ,
      poznamka character varying(100) NOT NULL,    
     opakuje boolean NOT NULL,    
     id_predmet integer NOT NULL REFERENCES predmet (id),    
     id_student integer NOT NULL REFERENCES student (id), 
     id_vyucovacia_hodina integer NOT NULL REFERENCES vyucovacia_hodina (id)    
);