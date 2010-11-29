<script type="text/javascript" src="js/set_priority.js"></script>

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
<?php if($read_only_semester != true)
echo '<p><a href="all/priorities/getPrevPriorities">Prebrať osobné časové priority z minulého roka</a></p><br />';
?>
<form class="priorityTable" style="margin: 0px;" method="post" action="all/priorities/save">
	<input type="hidden" name="semester_id" value="<?php echo $semester_id; ?>" />
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
				<div class="col editable <?php echo $raster[0][1]?>" id="priorities[1_1]"></div>
				<div class="col editable <?php echo $raster[0][2]?>" id="priorities[1_2]"></div>
				<div class="col editable <?php echo $raster[0][3]?>" id="priorities[1_3]"></div>
				<div class="col editable <?php echo $raster[0][4]?>" id="priorities[1_4]"></div>
				<div class="col editable <?php echo $raster[0][5]?>" id="priorities[1_5]"></div>
				<div class="col editable <?php echo $raster[0][6]?>" id="priorities[1_6]"></div>
				<div class="col editable <?php echo $raster[0][7]?>" id="priorities[1_7]"></div>
				<div class="col editable <?php echo $raster[0][8]?>" id="priorities[1_8]"></div>
				<div class="col editable <?php echo $raster[0][9]?>" id="priorities[1_9]"></div>
				<div class="col editable <?php echo $raster[0][10]?>" id="priorities[1_10]"></div>
				<div class="col editable <?php echo $raster[0][11]?>" id="priorities[1_11]"></div>
				<div class="col editable <?php echo $raster[0][12]?>" id="priorities[1_12]"></div>
				<div class="col editable <?php echo $raster[0][13]?>" id="priorities[1_13]"></div>
				<div class="col editable <?php echo $raster[0][14]?>" id="priorities[1_14]"></div>
				<div class="col editable <?php echo $raster[0][15]?>" id="priorities[1_15]"></div>
			</div>
			<div>
				<div class="col priorityTableHead">Ut</div>
				<div class="col editable <?php echo $raster[1][1]?>" id="priorities[2_1]"></div>
				<div class="col editable <?php echo $raster[1][2]?>" id="priorities[2_2]"></div>
				<div class="col editable <?php echo $raster[1][3]?>" id="priorities[2_3]"></div>
				<div class="col editable <?php echo $raster[1][4]?>" id="priorities[2_4]"></div>
				<div class="col editable <?php echo $raster[1][5]?>" id="priorities[2_5]"></div>
				<div class="col editable <?php echo $raster[1][6]?>" id="priorities[2_6]"></div>
				<div class="col editable <?php echo $raster[1][7]?>" id="priorities[2_7]"></div>
				<div class="col editable <?php echo $raster[1][8]?>" id="priorities[2_8]"></div>
				<div class="col editable <?php echo $raster[1][9]?>" id="priorities[2_9]"></div>
				<div class="col editable <?php echo $raster[1][10]?>" id="priorities[2_10]"></div>
				<div class="col editable <?php echo $raster[1][11]?>" id="priorities[2_11]"></div>
				<div class="col editable <?php echo $raster[1][12]?>" id="priorities[2_12]"></div>
				<div class="col editable <?php echo $raster[1][13]?>" id="priorities[2_13]"></div>
				<div class="col editable <?php echo $raster[1][14]?>" id="priorities[2_14]"></div>
				<div class="col editable <?php echo $raster[1][15]?>" id="priorities[2_15]"></div>
			</div>
			<div>
				<div class="col priorityTableHead">St</div>
				<div class="col editable <?php echo $raster[2][1]?>" id="priorities[3_1]"></div>
				<div class="col editable <?php echo $raster[2][2]?>" id="priorities[3_2]"></div>
				<div class="col editable <?php echo $raster[2][3]?>" id="priorities[3_3]"></div>
				<div class="col editable <?php echo $raster[2][4]?>" id="priorities[3_4]"></div>
				<div class="col editable <?php echo $raster[2][5]?>" id="priorities[3_5]"></div>
				<div class="col editable <?php echo $raster[2][6]?>" id="priorities[3_6]"></div>
				<div class="col editable <?php echo $raster[2][7]?>" id="priorities[3_7]"></div>
				<div class="col editable <?php echo $raster[2][8]?>" id="priorities[3_8]"></div>
				<div class="col editable <?php echo $raster[2][9]?>" id="priorities[3_9]"></div>
				<div class="col editable <?php echo $raster[2][10]?>" id="priorities[3_10]"></div>
				<div class="col editable <?php echo $raster[2][11]?>" id="priorities[3_11]"></div>
				<div class="col editable <?php echo $raster[2][12]?>" id="priorities[3_12]"></div>
				<div class="col editable <?php echo $raster[2][13]?>" id="priorities[3_13]"></div>
				<div class="col editable <?php echo $raster[2][14]?>" id="priorities[3_14]"></div>
				<div class="col editable <?php echo $raster[2][15]?>" id="priorities[3_15]"></div>
			</div>
			<div>
				<div class="col priorityTableHead">Št</div>
				<div class="col editable <?php echo $raster[3][1]?>" id="priorities[4_1]"></div>
				<div class="col editable <?php echo $raster[3][2]?>" id="priorities[4_2]"></div>
				<div class="col editable <?php echo $raster[3][3]?>" id="priorities[4_3]"></div>
				<div class="col editable <?php echo $raster[3][4]?>" id="priorities[4_4]"></div>
				<div class="col editable <?php echo $raster[3][5]?>" id="priorities[4_5]"></div>
				<div class="col editable <?php echo $raster[3][6]?>" id="priorities[4_6]"></div>
				<div class="col editable <?php echo $raster[3][7]?>" id="priorities[4_7]"></div>
				<div class="col editable <?php echo $raster[3][8]?>" id="priorities[4_8]"></div>
				<div class="col editable <?php echo $raster[3][9]?>" id="priorities[4_9]"></div>
				<div class="col editable <?php echo $raster[3][10]?>" id="priorities[4_10]"></div>
				<div class="col editable <?php echo $raster[3][11]?>" id="priorities[4_11]"></div>
				<div class="col editable <?php echo $raster[3][12]?>" id="priorities[4_12]"></div>
				<div class="col editable <?php echo $raster[3][13]?>" id="priorities[4_13]"></div>
				<div class="col editable <?php echo $raster[3][14]?>" id="priorities[4_14]"></div>
				<div class="col editable <?php echo $raster[3][15]?>" id="priorities[4_15]"></div>
			</div>
			<div>
				<div class="col priorityTableHead">Pia</div>
				<div class="col editable <?php echo $raster[4][1]?>" id="priorities[5_1]"></div>
				<div class="col editable <?php echo $raster[4][2]?>" id="priorities[5_2]"></div>
				<div class="col editable <?php echo $raster[4][3]?>" id="priorities[5_3]"></div>
				<div class="col editable <?php echo $raster[4][4]?>" id="priorities[5_4]"></div>
				<div class="col editable <?php echo $raster[4][5]?>" id="priorities[5_5]"></div>
				<div class="col editable <?php echo $raster[4][6]?>" id="priorities[5_6]"></div>
				<div class="col editable <?php echo $raster[4][7]?>" id="priorities[5_7]"></div>
				<div class="col editable <?php echo $raster[4][8]?>" id="priorities[5_8]"></div>
				<div class="col editable <?php echo $raster[4][9]?>" id="priorities[5_9]"></div>
				<div class="col editable <?php echo $raster[4][10]?>" id="priorities[5_10]"></div>
				<div class="col editable <?php echo $raster[4][11]?>" id="priorities[5_11]"></div>
				<div class="col editable <?php echo $raster[4][12]?>" id="priorities[5_12]"></div>
				<div class="col editable <?php echo $raster[4][13]?>" id="priorities[5_13]"></div>
				<div class="col editable <?php echo $raster[4][14]?>" id="priorities[5_14]"></div>
				<div class="col editable <?php echo $raster[4][15]?>" id="priorities[5_15]"></div>
			</div>
		</div>
		
		<div id="chooser">
			<div>
				<div class="col color5 priorityTableNote" style="width: 212px;">Výber priority:</div>
				<div id="sel_a" class="col color_a sel" style="width: 150px;">Preferované</div>
				<div id="sel_b" class="col color_b" style="width: 150px;">OK</div>
				<div id="sel_c" class="col color_c" style="width: 150px;">Nevyhovujúce</div>
			</div>
			<div>
                <div class="color5 commentNote" style="width: 212px;">Komentár:</div>
                <div class="color5 commentNote" style="width: 455px;">
                    <textarea style="height:62px;" name="comment" rows="3" cols="50"><?php echo $comment; ?></textarea>
                </div>
			</div>
			<div>
             <?php
            if($read_only_semester != true)
                echo "<input type='submit' value='Ulož'/>";
            ?>
      </div>
		</div>
	</div>
<?php
	foreach( $priorities as $priority) 
	{
    	for($i=$priority['start'];$i<=$priority['end'];$i++) 
    	{
        	$raster[intval($priority['day'])-1][intval($i)] = $colors[$priority['type_id']];
        	echo "<input type='hidden' value='{$priority['type_id']}' name='priorities[{$priority['day']}_$i]' />";
    	}
	}
?>
</form>

