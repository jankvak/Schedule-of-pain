-- Table: software

-- DROP TABLE software;

CREATE TABLE software
(
  id bigserial NOT NULL,
  "name" character varying(50) NOT NULL,
  CONSTRAINT software_pkey PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE software OWNER TO "Scheduler";
