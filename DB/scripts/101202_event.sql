-- removes column end and adds column event_type to event
ALTER TABLE event DROP COLUMN "end";
ALTER TABLE event ADD COLUMN event_type bigint;
ALTER TABLE event ALTER COLUMN event_type SET NOT NULL;
ALTER TABLE event ALTER COLUMN event_type SET DEFAULT 1;
COMMENT ON COLUMN event.event_type IS '0 - lecture, 1 - exercise';
