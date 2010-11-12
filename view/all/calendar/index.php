<?php 
/*
    - pridany eventSource na gcal (chcelo by to filter na sviatky kde je volno ...)
Custom locales:
    - timeFormat aby zobrazoval 24 hodinovy format aj s minutami, zaciatocny aj koncovy cas
    - axisFormat nastavuje 24h format pri weekAgenda
    - allDayText mení hlášku 
*/
?>
<script type="text/javascript">
$(document).ready(function() {
			
    // page is now ready, initialize the calendar...
    $('#calendar').fullCalendar({
    	theme: true, //ak je true, pouzije sa theme v css/redmond/redmond.css
		editable: false, //zatial mozu vsetci len prehliadat
		timeFormat: 'H:mm { - H:mm}',
		axisFormat: 'H',
		allDayText: 'celý deň',
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		eventSources: [$.fullCalendar.gcalFeed(
				  "http://www.google.com/calendar/feeds/slovak__sk%40holiday.calendar.google.com/public/basic")],
		events:
        <?php
            //skonvertujeme do json syntaxe - funguje len s UTF-8 kodovanim			       
			echo json_encode($events);
		?> 
		});	
});
</script>
<div id='loading' style='display: none'>loading...</div>
<div id="calendar" style="margin: 0 auto;"></div>
