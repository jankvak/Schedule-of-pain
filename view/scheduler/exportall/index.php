<h5>Export požiadaviek</h5><br>
<a href="scheduler/exportall/createdocall">Exportovať všetky požiadavky vo formáte MS Word</a><br>
<a href="scheduler/exportall/createcsv">Exportovať všetky požiadavky vo formáte CSV</a><br><br>
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
	foreach ($poziadavky as $poziadavka):
	?>
		<tr>
			<td width="10%" align="center"><a
				href="<?php echo "scheduler/exportall/createdoc/{$poziadavka['id']}"; ?>">
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