<h2>Evidencia vybavenia</h2>
<table class="sorted-table paged-table {pagesizes:[5,10,15], selpagesize: 0}">
	<thead>
		<tr>
			<th>Typ</th>
			<th>Prenosné</th>
			<th>Poznámka</th>
			<th>Akcie</th>
		</tr>
	</thead>
	<tbody>

	<?php

	foreach($equipment as $equip) {
		echo "<tr id='row_{$equip["id"]}'>";
			echo "<td class='name:typ'>" . $equip["typ"] . "</td>";
			if($equip["prenosne"] == "t") {
				echo "<td align='center'><input type='checkbox' name='prenosne' checked='checked' disabled='disabled'/></td>";
			} else {
				echo "<td align='center'><input type='checkbox' name='prenosne' disabled='disabled'/></td>";
			}
			echo "<td class='name:poznamka'>" . $equip["poznamka"] . "</td>";
			echo "<td class='action'><a href='ape/equipment/edit/{$equip["id"]}'>Upraviť</a><br/><a href='ape/equipment/delete/{$equip["id"]}'>Odstrániť</a></td>";
		echo "</tr>";
	}

	?>

	</tbody>
</table>
<p>
<br/><a href='ape/equipment/add'>Pridať nové vybavenie</a>
</p>