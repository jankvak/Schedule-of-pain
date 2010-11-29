<h2>Evidencia miestností</h2>
<p>
<a href='ape/rooms/add'>Pridaj miestnosť</a>
</p>
<table class="sorted-table paged-table filtered {pagesizes:[10,25,50], selpagesize: 0}">
	<thead>
		<tr>
			<th>Názov</th>
			<th>Kapacita</th>
			<th>Poznámka</th>
			<th>Typ</th>
			<th>Akcie</th>
		</tr>
	</thead>
	<tbody>
	
	<?php //print_r($rooms); die(); ?>

	<?php foreach($rooms as $r) { ?>
		<tr id='row_<?php echo $r["id"] ?>'>
			<td class='nazov'> <?php echo $r["nazov"] ?> </td>
			<td class='kapacita'> <?php echo $r["kapacita"] ?> </td>
			<td class='poznamka'> <?php echo $r["poznamka"] ?> </td>
			<td class='typ'> <?php echo $r["typ"] ?> </td>
			<td class='action'><a href='ape/rooms/edit/<?php echo $r["id"]?>'>Upraviť</a><br/><a href='ape/rooms/delete/<?php echo $r["id"]?>'>Odstrániť</a></td>
		</tr>
	<?php } ?>

	</tbody>
</table>


