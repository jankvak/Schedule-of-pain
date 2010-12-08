ALTER TABLE time_event_exclusion ADD COLUMN "order" integer;
ALTER TABLE time_event_exclusion ALTER COLUMN "order" SET NOT NULL;
COMMENT ON COLUMN time_event_exclusion."order" IS 'if 1st lecture is excluded, then 0, if 2nd, then 1, etc.';

ALTER TABLE time_event_exclusion DROP COLUMN date;

ALTER TABLE time_event ALTER COLUMN "start" DROP NOT NULL
ALTER TABLE time_event ALTER COLUMN "end" DROP NOT NULL

ALTER TABLE time_event ALTER COLUMN recur_freq SET DEFAULT 7;
ALTER TABLE time_event ALTER COLUMN recur_count SET DEFAULT 13;
