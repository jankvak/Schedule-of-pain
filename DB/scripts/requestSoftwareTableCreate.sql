-- Table: request_software

-- DROP TABLE request_software;

CREATE TABLE request_software
(
  id bigserial NOT NULL,
  id_reqest bigint NOT NULL,
  id_software bigint NOT NULL,
  CONSTRAINT request_software_pkey PRIMARY KEY (id),
  CONSTRAINT fk_request_software_request FOREIGN KEY (id_reqest)
      REFERENCES request (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_request_software_software FOREIGN KEY (id_software)
      REFERENCES software (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);
ALTER TABLE request_software OWNER TO "Scheduler";
