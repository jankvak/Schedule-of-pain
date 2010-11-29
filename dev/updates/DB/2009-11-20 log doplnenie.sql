ALTER TABLE log ADD COLUMN zastupuje VARCHAR(50) DEFAULT '';
COMMENT ON COLUMN log.zastupuje IS 'kto ho zastupoval';