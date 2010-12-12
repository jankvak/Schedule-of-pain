CREATE OR REPLACE FUNCTION "SemesterDescription"(param_id_semester bigint)
  RETURNS text AS
$BODY$begin
  return(
    SELECT semester.year || '/' || semester.year+1 ||
           ' - ' ||
           CASE
             WHEN semester_order LIKE '1' THEN 'ZS'
             ELSE 'LS'
           END
      FROM semester
     WHERE semester.id = param_id_semester);
end;$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION "SemesterDescription"(bigint) OWNER TO "Scheduler";
