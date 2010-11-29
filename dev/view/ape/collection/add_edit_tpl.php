<?php /* napisem to radsej sem ... ren maly zasral s menom "duration:''" sposoboval ze timepicker sa skryval pod kalendarom*/ ?>
<script type="text/javascript">
$(document).ready(function(){
	$("#zaciatok, #koniec").datepicker({
		showTime: true,
		time24h: true,
		stepMinutes: 15,
    duration: '', 
    constrainInput: false		
	});
});
</script>
<form action="ape/collection/save" method="post">
	<input type="hidden" name="id" value="<?php echoParam($akcia["id"]); ?>">
	<p>
		<label for="zaciatok">Začiatok: </label>
		<input id="zaciatok" type="text" name="zaciatok" size="16" maxlength="16" value="<?php echoParam($akcia["zaciatok"]); ?>"/>
	</p>
	<p>
		<label for="koniec">Koniec: </label>
		<input id="koniec" type="text" name="koniec" size="16" maxlength="16" value="<?php echoParam($akcia["koniec"]); ?>"/>
	</p>
	<p>
		<label for="id_semester">Pre semester: </label>
		<select name="id_semester">
		<?php 
			foreach ($semestre as $semester)
			{
				$rok1 = $semester["rok"];
				$rok2 = $rok1+1;
				$sem = $semester["semester"] == 1 ? "ZS" : "LS";
				$sel = (isset($akcia["id_semester"]) && $akcia["id_semester"]==$semester["id"]) ? " selected='selected'" : "";
				echo "<option value=\"{$semester["id"]}\"{$sel}>{$rok1}/{$rok2} - $sem</option>";				
			}			
		?>
		</select>
	</p>
	<p>
		<input type="submit" value="Ulož"/>
	</p>
</form>
<p>
	<a href="ape/collection/index">Naspäť</a>
</p>