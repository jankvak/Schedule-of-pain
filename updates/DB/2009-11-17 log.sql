CREATE TABLE log(
    id BIGSERIAL,
    username character varying(50),
    udalost character varying(255),
    cas timestamp,
    PRIMARY KEY(id)
);