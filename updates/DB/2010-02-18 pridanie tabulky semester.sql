CREATE TABLE semester(
	id serial,
	rok smallint,
	semester smallint,
	zac_uc date,
	kon_uc date,
	zac_skus date,
	zac_opr date,
	kon_skus date
);
COMMENT ON COLUMN semester.rok IS 'akademicky rok, 2009 pre 2009/2010';
COMMENT ON COLUMN semester.semester IS 'ZS=1, LS=2';
COMMENT ON COLUMN semester.zac_uc IS 'zaciatok ucenia v semestri';
COMMENT ON COLUMN semester.kon_uc IS 'koniec ucenia v semestri';
COMMENT ON COLUMN semester.zac_skus IS 'zaciatok skuskoveho';
COMMENT ON COLUMN semester.zac_opr IS 'zaciatok opravakov';
COMMENT ON COLUMN semester.kon_skus IS 'koniec skuskoveho';
