ALTER TABLE request_room ALTER COLUMN id SET DEFAULT nextval(('public.request_room_id_seq'::text)::regclass);
-- Sequence: request_room_id_seq

-- DROP SEQUENCE request_room_id_seq;

CREATE SEQUENCE request_room_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
ALTER TABLE request_room_id_seq OWNER TO "Scheduler";
