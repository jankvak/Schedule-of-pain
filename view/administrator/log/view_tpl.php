<table id="table" class="sorted-table paged-table {sortlist: [[4,1]], pagesizes:[10,25,50], selpagesize: 0}">
	<thead>
		<tr>
			<th>Používateľ</th>
			<th>Zastupuje</th>
			<th>Udalosť</th>
			<th>IP</th>
			<th class="{sorter: 'dates-sk'}">Čas</th>
		</tr>
	</thead>
	<tbody>
<?php
	foreach ($events as $event):
?>
		<tr>
			<td><?php echo $event["username"];?></td>
			<td><?php echo empty($event["zastupuje"]) ? "&nbsp;" : $event["zastupuje"];?></td>
			<td><?php echo nl2br($event["udalost"]);?></td>
			<td><?php echo $event["ip"];?></td>
			<td><?php echo $event["cas"];?></td>
		</tr>
<?php 
	endforeach;
?>
	</tbody>
</table>
