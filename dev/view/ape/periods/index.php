<h2>Správa semestrov</h2>
<p>
	<a href="ape/periods/add">Pridať nový semester</a>
</p>
<?php 
	if (empty($semestre)){
?>
<p class="info">Zatiaľ neboli nadefinované žiadne semestre</p>
<?php 
	}else{
?>
<table class="sorted-table paged-table {sortlist: [[0,1]], pagesizes:[5,10,15], selpagesize: 0}">
	<thead>
		<tr>
			<th>Akademický rok</th>
			<th>Semester</th>
			<th>Začiatok semestra</th>
			<th>Koniec semestra</th>
			<th>Začiatok skúškového obdobia</th>
			<th>Začiatok opravných skúšok</th>
			<th>Koniec skúškového obdobia</th>
			<th>Akcia</th>
		</tr>
	</thead>
	<tbody>
		<?php 
			foreach ($semestre as $semester){
		?>
		<tr>	
			<td><?php echo $semester["rok"];?>/<?php echo $semester["rok"]+1; ?></td>
			<td>
				<?php 
					switch ($semester["semester"])
					{
						case 1: echo "ZS"; break;
						case 2; echo "LS"; break;
						default: echo "Nepl. hodnota !";
					}; 
				?>
			</td>
			<td><?php echo $semester["zac_uc"]; ?></td>
			<td><?php echo $semester["kon_uc"]; ?></td>
			<td><?php echo $semester["zac_skus"]; ?></td>
			<td><?php echo $semester["zac_opr"]; ?></td>
			<td><?php echo $semester["kon_skus"]; ?></td>
			<td><a href="ape/periods/edit/<?php echo $semester["id"];?>">Upraviť</a></td>
		</tr>
		<?php 
			}
		?>
	</tbody>
</table>
<?php
	}
?>