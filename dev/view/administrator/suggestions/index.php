<h2>Pripomienky k systému</h2>
<p><a href='all/suggestion/add'>Pridať novú pripomienku</a></p>
<form class="suggestion_state" method="post" action="administrator/suggestions/save">
<table class="sorted-table paged-table {sortlist: [[0,1]], pagesizes:[5,10,15], selpagesize: 0}">
	<thead>
		<tr>
			<th align="center" class="{sorter: 'dates-sk'}">Čas vloženia</th>
			<th align="center">Typ</th>
			<th>Text</th>
			<th>Vložil</th>
			<th align="center">Stav</th>
			<th>Akcia</th>
		</tr>
	</thead>
	<tbody>

	<?php
	foreach($suggestions as $suggestion) {
		echo "<tr>";
			echo "<td align=\"center\">" . date("d.m.Y H:i", $suggestion["casova_peciatka"]) . "</td>";
			echo "<td align=\"center\">" . $suggestion["typ"] . "</td>";
            // nezabudnut nahradit \n za <br>
			echo "<td>" . nl2br($suggestion["text"]) . "</td>";
			echo "<td>" . $suggestion["pedagog_meno"] . "</td>";
			echo "<td align=\"center\">" . $suggestion["nazovStavu"] . "</td>";
			echo "<td class='action'><a href='administrator/suggestions/delete/{$suggestion["id"]}'>Vymazať</a><br>";
			echo "<a href='administrator/suggestions/edit/{$suggestion["id"]}'>Upraviť</a></td>";
		echo "</tr>";
	}
  ?>
	</tbody>
</table>
<p><input type="submit" value="Uložiť zmeny" /></p>
</form>
