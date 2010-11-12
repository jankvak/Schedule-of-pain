ALTER TABLE pripomienka ADD COLUMN stav smallint;
ALTER TABLE pripomienka ALTER COLUMN stav SET DEFAULT 1;
COMMENT ON COLUMN pripomienka.stav IS 'stav pripomienky - ci je nova, vyriesena a pod. Nazvy stavovov su nadeklarovane v prislusnom modeli.';
