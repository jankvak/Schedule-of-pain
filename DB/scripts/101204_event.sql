ALTER TABLE event ADD COLUMN confirmed boolean;
ALTER TABLE event ALTER COLUMN confirmed SET NOT NULL;
ALTER TABLE event ALTER COLUMN confirmed SET DEFAULT false;
COMMENT ON COLUMN event.confirmed IS 'TRUE if the event is in schedule
FALSE if event is in request state';

CREATE SEQUENCE request_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
ALTER TABLE request_id_seq OWNER TO "Scheduler";
ALTER TABLE request ALTER COLUMN id SET DEFAULT nextval(('public.request_id_seq'::text)::regclass);
