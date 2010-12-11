CREATE OR REPLACE FUNCTION "IsNthEventExcluded"(parameter_id_event bigint, parameter_order integer)
  RETURNS boolean AS
$BODY$begin
return
	EXISTS(SELECT 1
	FROM time_event_exclusion tee
	        JOIN time_event te ON tee.id_time_event = te.id
	        JOIN event_time_event e2t ON te.id = e2t.id_time_event
	WHERE e2t.id_event = parameter_id_event
	AND tee.order = parameter_order);
end$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION "IsNthEventExcluded"(bigint, integer) OWNER TO "Scheduler";
