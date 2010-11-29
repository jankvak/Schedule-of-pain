<h2>Rozvrhové akcie</h2>
<p><a href="ape/collection/add">Pridať novú rozvrhovú akciu</a></p>
<table class="sorted-table paged-table filtered {sortlist: [[0,1]]}">
	<thead>
		<tr>
			<th>Začiatok</th>
			<th>Koniec</th>
			<th>Semester</th>
			<th>Akcie</th>
		</tr>
	</thead>
	<tbody>
	<?php
		foreach ($akcie as $akcia):
	?>
		<tr>
			<td><?php echo $akcia["zaciatok"]; ?></td>		
			<td><?php echo $akcia["koniec"]; ?></td>		
			<td><?php echo $akcia["semester"]; ?></td>
			<td>
				<a href="ape/collection/edit/<?php echo $akcia["id"]?>">Upraviť</a> |
				<a href="ape/collection/delete/<?php echo $akcia["id"]?>">Zmazať</a>
			</td>		
		</tr>
	<?php 
		endforeach;
	?>
	</tbody>
</table>
