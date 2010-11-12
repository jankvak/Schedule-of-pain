<?php $raster=array(array(),array(),array(),array(),array());
$defColor;
$colors=array("","","","","","","","","","");
foreach( $types as $type)
{    
    if($type['default']=='t')
    {
        $defColor=$type['color'];
    }
    $colors[$type['id']]=$type['color'];
}
for($i=0;$i<5;$i++)
    for($j=1;$j<=15;$j++)
        $raster[$i][$j]=$defColor;


foreach( $priorities as $priority)
{

    for($i=$priority['start'];$i<=$priority['end'];$i++)
    {
        $raster[intval($priority['day'])-1][intval($i)] = $colors[$priority['type_id']];

    }
}

?>
<h2>Osobné časové priority</h2>
<form class="priorityTable" style="margin: 0px;" method="post" action="all/priorities/save">
	<div>
		<div class="table" style="display: block;">
			<div>
				<div class="col_top"></div>
				<div class="col_top priorityTableHead">07:00 07:50</div>
				<div class="col_top priorityTableHead">08:00 08:50</div>
				<div class="col_top priorityTableHead">09:00 09:50</div>
				<div class="col_top priorityTableHead">10:00 10:50</div>
				<div class="col_top priorityTableHead">11:00 11:50</div>
				<div class="col_top priorityTableHead">12:00 12:50</div>
				<div class="col_top priorityTableHead">13:00 13:50</div>
				<div class="col_top priorityTableHead">14:00 14:50</div>
				<div class="col_top priorityTableHead">15:00 15:50</div>
				<div class="col_top priorityTableHead">16:00 16:50</div>
				<div class="col_top priorityTableHead">17:00 17:50</div>
				<div class="col_top priorityTableHead">18:00 18:50</div>
				<div class="col_top priorityTableHead">19:00 19:50</div>
				<div class="col_top priorityTableHead">20:00 20:50</div>
				<div class="col_top priorityTableHead">21:00 21:50</div>
			</div>
			<div>
				<div class="col priorityTableHead">Po</div>
				<div class="col editable <?php echo $raster[0][1]?>"></div>
				<div class="col editable <?php echo $raster[0][2]?>"></div>
				<div class="col editable <?php echo $raster[0][3]?>"></div>
				<div class="col editable <?php echo $raster[0][4]?>"></div>
				<div class="col editable <?php echo $raster[0][5]?>"></div>
				<div class="col editable <?php echo $raster[0][6]?>"></div>
       			<div class="col editable <?php echo $raster[0][7]?>"></div>
				<div class="col editable <?php echo $raster[0][8]?>"></div>
				<div class="col editable <?php echo $raster[0][9]?>"></div>
				<div class="col editable <?php echo $raster[0][10]?>"></div>
				<div class="col editable <?php echo $raster[0][11]?>"></div>
				<div class="col editable <?php echo $raster[0][12]?>"></div>
				<div class="col editable <?php echo $raster[0][13]?>"></div>
				<div class="col editable <?php echo $raster[0][14]?>"></div>
				<div class="col editable <?php echo $raster[0][15]?>"></div>
			</div>
			<div>
				<div class="col priorityTableHead">Ut</div>
				<div class="col editable <?php echo $raster[1][1]?>"></div>
				<div class="col editable <?php echo $raster[1][2]?>"></div>
				<div class="col editable <?php echo $raster[1][3]?>"></div>
				<div class="col editable <?php echo $raster[1][4]?>"></div>
				<div class="col editable <?php echo $raster[1][5]?>"></div>
				<div class="col editable <?php echo $raster[1][6]?>"></div>
				<div class="col editable <?php echo $raster[1][7]?>"></div>
				<div class="col editable <?php echo $raster[1][8]?>"></div>
				<div class="col editable <?php echo $raster[1][9]?>"></div>
				<div class="col editable <?php echo $raster[1][10]?>"></div>
				<div class="col editable <?php echo $raster[1][11]?>"></div>
				<div class="col editable <?php echo $raster[1][12]?>"></div>
				<div class="col editable <?php echo $raster[1][13]?>"></div>
				<div class="col editable <?php echo $raster[1][14]?>"></div>
				<div class="col editable <?php echo $raster[1][15]?>"></div>
			</div>
			<div>
				<div class="col priorityTableHead">St</div>
				<div class="col editable <?php echo $raster[2][1]?>"></div>
				<div class="col editable <?php echo $raster[2][2]?>"></div>
				<div class="col editable <?php echo $raster[2][3]?>"></div>
				<div class="col editable <?php echo $raster[2][4]?>"></div>
				<div class="col editable <?php echo $raster[2][5]?>"></div>
				<div class="col editable <?php echo $raster[2][6]?>"></div>
				<div class="col editable <?php echo $raster[2][7]?>"></div>
				<div class="col editable <?php echo $raster[2][8]?>"></div>
				<div class="col editable <?php echo $raster[2][9]?>"></div>
				<div class="col editable <?php echo $raster[2][10]?>"></div>
				<div class="col editable <?php echo $raster[2][11]?>"></div>
				<div class="col editable <?php echo $raster[2][12]?>"></div>
				<div class="col editable <?php echo $raster[2][13]?>"></div>
				<div class="col editable <?php echo $raster[2][14]?>"></div>
				<div class="col editable <?php echo $raster[2][15]?>"></div>
			</div>
			<div>
				<div class="col priorityTableHead">Št</div>
				<div class="col editable <?php echo $raster[3][1]?>"></div>
				<div class="col editable <?php echo $raster[3][2]?>"></div>
				<div class="col editable <?php echo $raster[3][3]?>"></div>
				<div class="col editable <?php echo $raster[3][4]?>"></div>
				<div class="col editable <?php echo $raster[3][5]?>"></div>
				<div class="col editable <?php echo $raster[3][6]?>"></div>
				<div class="col editable <?php echo $raster[3][7]?>"></div>
				<div class="col editable <?php echo $raster[3][8]?>"></div>
				<div class="col editable <?php echo $raster[3][9]?>"></div>
				<div class="col editable <?php echo $raster[3][10]?>"></div>
				<div class="col editable <?php echo $raster[3][11]?>"></div>
				<div class="col editable <?php echo $raster[3][12]?>"></div>
				<div class="col editable <?php echo $raster[3][13]?>"></div>
				<div class="col editable <?php echo $raster[3][14]?>"></div>
				<div class="col editable <?php echo $raster[3][15]?>"></div>
			</div>
			<div>
				<div class="col priorityTableHead">Pia</div>
				<div class="col editable <?php echo $raster[4][1]?>"></div>
				<div class="col editable <?php echo $raster[4][2]?>"></div>
				<div class="col editable <?php echo $raster[4][3]?>"></div>
				<div class="col editable <?php echo $raster[4][4]?>"></div>
				<div class="col editable <?php echo $raster[4][5]?>"></div>
				<div class="col editable <?php echo $raster[4][6]?>"></div>
				<div class="col editable <?php echo $raster[4][7]?>"></div>
				<div class="col editable <?php echo $raster[4][8]?>"></div>
				<div class="col editable <?php echo $raster[4][9]?>"></div>
				<div class="col editable <?php echo $raster[4][10]?>"></div>
				<div class="col editable <?php echo $raster[4][11]?>"></div>
				<div class="col editable <?php echo $raster[4][12]?>"></div>
				<div class="col editable <?php echo $raster[4][13]?>"></div>
				<div class="col editable <?php echo $raster[4][14]?>"></div>
				<div class="col editable <?php echo $raster[4][15]?>"></div>
			</div>
		</div>
		
		<div id="chooser">
			<div>
				<div class="col color5 priorityTableNote" style="width: 212px;">Výber priority:</div>
				<div id="sel_a" class="col color_a" style="width: 150px;">Preferované</div>
				<div id="sel_b" class="col color_b" style="width: 150px;">OK</div>
				<div id="sel_c" class="col color_c" style="width: 150px;">Nevyhovujúce</div>
			</div>
			<div>
                <div class="color5 commentNote" style="width: 212px;">Komentár:</div>
                <div class="color5 commentNote" style="width: 455px;" ><textarea style="height:62px;" name="comment" readonly="readonly" rows="3" cols="50"><?php echo $comment; ?></textarea></div>
			</div>
			<div>
      </div>
		</div>
	</div>
</form>

