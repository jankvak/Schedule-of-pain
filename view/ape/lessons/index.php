<h2>Priradenie výučby pedagógom</h2>
<form class="teacher_lesson" method="post" action="ape/lessons/save">
<table class="sorted-table {sortlist: [[1,0]], pagesizes:[15,30,60], selpagesize: 0}">
	<thead>
		<tr>
			<th>Kód predmetu</th>
			<th>Názov predmetu</th>
			<th>Študijný program</th>
			<th>Garant</th>
		</tr>
	</thead>
	<tbody>
	<?php
	// priprav si associativne pole aby sa lahsie dalo zistovat priradenie
	$priradenia = array();
	foreach ($priradenie as $prir)
	{
		$priradenia[$prir["id_predmet"]] = $prir["id_pedagog"];
	}
	$i=0;
	foreach ($predmet as $pr){
		$i++;
		$str = "<tr><td>{$pr['kod']}</td>";
		$str .= "<td>{$pr['nazov']}</td>";
		$str .= "<td>{$pr['sp_nazov']}</td>";
		$str .= "<td><input type='hidden' name='garant[$i][id_course]' value='{$pr['id']}'/>";
		$str .= "<select name='garant[$i][id_garant]'>";
		$str .= "<option value='0'>Vyberte garanta</option>";
		foreach ($garanti as $gar){
			$name = $gar['priezvisko']." ".$gar['meno'].", ".$gar['tituly_pred']." ".$gar['tituly_za'];
			$str=$str."<option value='{$gar['id']}'";
			if ($priradenia[$pr["id"]] == $gar["id"]) $str .= " selected='selected'"; 
			$str .= ">{$name}</option>";
		}
		$str .= "</select></td></tr>";
		echo $str;
	}
	?>
	</tbody>        
</table>
<p><input type="submit" value="Uložiť zmeny" /></p>
</form>
