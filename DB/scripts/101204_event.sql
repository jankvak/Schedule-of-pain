ALTER TABLE event ADD COLUMN confirmed boolean;
ALTER TABLE event ALTER COLUMN confirmed SET NOT NULL;
ALTER TABLE event ALTER COLUMN confirmed SET DEFAULT false;
COMMENT ON COLUMN event.confirmed IS 'TRUE if the event is in schedule
FALSE if event is in request state';
