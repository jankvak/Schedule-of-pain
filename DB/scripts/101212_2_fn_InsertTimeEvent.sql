ALTER TABLE request DROP CONSTRAINT request_requester;

CREATE OR REPLACE FUNCTION "InsertTimeEvent"(param_id_course bigint, param_id_semester bigint, param_week_day integer, param_hour integer, param_duration integer, param_type bigint)
  RETURNS bigint AS
$BODY$declare
  var_id_time_event bigint;
  var_id_event bigint;
  var_id_request bigint;
  t_start timestamp;
begin
  SELECT tuition_start INTO t_start
    FROM semester
   WHERE semester.id = param_id_semester;

  t_start = date_trunc('week', t_start);
  t_start = t_start + param_week_day*(interval '1 day');
  t_start = t_start + param_hour*(interval '1 hour');

  INSERT INTO time_event(id, start, "end")
      SELECT nextval('time_event_id_seq'),
             t_start,
             t_start + param_duration * (interval '1 hour')
  RETURNING id INTO var_id_time_event;

  INSERT INTO event(id, id_course, id_semester, event_type)
  VALUES (DEFAULT, param_id_course, param_id_semester, param_type)
  RETURNING id INTO var_id_event;

  INSERT INTO event_time_event(id_event, id_time_event)
  VALUES (var_id_event, var_id_time_event);

  INSERT INTO request(id, id_event)
  VALUES (DEFAULT, var_id_event)
  RETURNING id INTO var_id_request;

  return var_id_request;
end;$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION "InsertTimeEvent"(bigint, bigint, integer, integer, integer, bigint) OWNER TO "Scheduler";

CREATE OR REPLACE VIEW select_request AS 
 SELECT request.id, event.id_semester, event.id_course, event.event_type, date_part('dow'::text, t_e.start) - 1::double precision AS week_day, date_part('hour'::text, t_e.start) AS hours, date_part('hour'::text, t_e."end" - t_e.start) AS duration
   FROM request
   JOIN event ON request.id_event = event.id
   JOIN event_time_event t2e ON event.id = t2e.id_event
   JOIN time_event t_e ON t2e.id_time_event = t_e.id
  WHERE t_e.start IS NOT NULL;

ALTER TABLE select_request OWNER TO postgres;
