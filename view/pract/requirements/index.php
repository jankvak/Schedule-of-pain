<h2>Vyberte predmet, ktorého požiadavky chcete zadať</h2>

<?php
    // split poziadaviek do zadanych a nezadanych
    $zadane = $nezadane = array();
	foreach($courses as $course)
	{
		if ($course["meta_poziadavka"]) $zadane[] = $course;
		else $nezadane[] = $course;
	}

	if(empty($courses)) {
        echo "<p class='error' >Zatiaľ nemáte priradené žiadne predmety</p>";
    }
	else {
		if (!empty($nezadane) && $read_only_semester != true){
?>
<h5>Predmety bez zadaných požiadaviek</h5>
<table class="inplace"> 
	<tbody>
		<?php foreach($nezadane as $course) { ?>
		<tr>
			<td width="35%"><a href="pract/requirements/edit/<?php echo $course['id'] ?>"><?php echo $course['nazov'] ?></a></td>
			<td></td>
		</tr>
		<?php }?>
	</tbody>
</table>
<br><br><br>
<?php 
		}// koniec nezadanych
		
		if ($zadane){
?>
<h5>Predmety so zadanými požiadavkami</h5>
<table class="inplace">
	<thead>
		<tr>
			<th>Predmet</th>
			<th>Autor požiadavky</th>
			<th>Akcia</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($zadane as $course) { ?>
		<tr>
			<td width="30%"><?php echo $course['nazov'] ?></td> 
			<td width="50%" align="center"><?php echo "{$course["meta_poziadavka"]["pedagog"]} o {$course["meta_poziadavka"]["cas_pridania"]}"; ?></td>
			<td width="10%" align="center">
            	<a href="pract/requirements/edit/<?php echo $course["id"]; ?>"><?php
            echo $read_only_semester == true ? "prezerať" : "upraviť"?></a><br/>
          	</td>
		</tr>
		<?php }?>

	</tbody>
</table>
<?php 
		}//koniec zadanych
	}//koniec poziadaviek
?>

