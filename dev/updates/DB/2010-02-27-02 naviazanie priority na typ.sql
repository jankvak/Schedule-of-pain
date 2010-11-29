-- pridanie constraintu PK<->FK pre priorita vyucby - typ_id

ALTER TABLE priorita_vyucby ADD CONSTRAINT priorita_vyucby_type_id_fkey FOREIGN KEY (type_id) REFERENCES priorita_typ(id) ON UPDATE CASCADE ON DELETE RESTRICT;