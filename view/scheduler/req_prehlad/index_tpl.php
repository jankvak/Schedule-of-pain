<?php

// identifikuj pohlad podla toho co zobrazujeme
if (isset($poziadavky_prednasky) && !isset($poziadavky_cvicenia)) 		$action = "prednaska";
else if (isset($poziadavky_cvicenia) && !isset($poziadavky_prednasky)) 	$action = "cvicenie";
else if (isset($poziadavky_cvicenia) && isset($poziadavky_prednasky)) 	$action = "spolocny";

function showRequirements($poziadavky, $typ, $all) {
	global $index;
	if ($typ == 'prednaska') {
		$title 		= "Požiadavky na prednášky";
		$controller = "scheduler/req_prednaska";
	}
	else if ($typ == 'cvicenie') {
		$title 		= "Požiadavky na cvičenia";
		$controller = "scheduler/req_cvicenie";
	}

	if ($all) {
		$index = "index";
		$text = "Zobrazenie najnovších požiadaviek k jednotlivým predmetom";
	}
	else {
		$index = "index_all";
		$text = "Zobrazenie aj starších požiadaviek k jednotlivým predmetom";
	}
	?>
<h5><?php echo $title; ?></h5>
<?php echo "<p><a href=\"{$controller}/{$index}\">$text</a></p>"; ?>
<table class="sorted-table paged-table filtered {sortlist:[[0,0]], pagesizes:[25,50,100], selpagesize: 0}">
	<thead>
		<tr>
			<th class="uzas">Predmet</th>
			<th class="uzas">Skratka</th>
			<th class="uzas">Semester</th>
			<th class="uzas {sorter: false}">Zadal</th>
			<th class="uzas {sorter: 'dates-sk'}">Dátum</th>
		</tr>
	</thead>
	<tbody>
	<?php
    if (empty($poziadavky))
    {
        echo "<tr><td colspan=\"5\">Zatiaľ nie sú evidované žiadne požiadavky.</td></tr>";
    }
	foreach ($poziadavky as $poziadavka):
	?>
		<tr>
			<td width="10%" align="center"><a
				href="<?php echo "{$controller}/show/{$poziadavka["meta_poziadavka"]['id']}"; ?>">
				<?php echo $poziadavka["nazov"]; ?> </a></td>
			<td width="10%" align="center"><?php echo $poziadavka["skratka"]; ?></td>
			<td width="10%" align="center"><?php echo $poziadavka["semester"]; ?></td>
			<td width="10%" align="center"><?php echo $poziadavka["meta_poziadavka"]["pedagog"]; ?></td>
			<td width="10%" align="center"><?php echo $poziadavka["meta_poziadavka"]["cas_pridania"]; ?></td>
		</tr>
		<?php
		endforeach;
		?>
	</tbody>
</table>
		<?php
} // koniec funkcie showRequirements

// na zaklade dodanych dat zobraz vhodne tabulky
if ($action == "spolocny" || $action == "prednaska") showRequirements($poziadavky_prednasky, "prednaska", !isset($index));
if ($action == "spolocny" || $action == "cvicenie") showRequirements($poziadavky_cvicenia, "cvicenie", !isset($index));

?>
