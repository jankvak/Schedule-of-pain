<script type="text/javascript" src="js/students_count.js"></script>
<div>
	<div class="row">
		<div class="left_side">
			<b id="students_count_header" title="Klikni pre zobrazenie">Počet zapísaných študentov:</b>
		</div>
	</div>
	<div id="students_count" class="row">
		<?php
			for ($i=0;$i<count($student_count_info);$i++) {
				echo "<div class='left_side'>".$student_count_info[$i]['nazov']."</div>";
				echo "<div class='count'>".$student_count_info[$i]['rocnik'].". ročník</div>";;
				echo "<div class='count'>".$student_count_info[$i]['student_count']."</div>";;
			}
			
			if (empty($student_count_info))
			{
				echo "<div class='left_side' style='width: 500px;'>predmet nemá zapísaný žiaden študent</div>";
			}
		?>
    </div>
    <div class="row" style="height:20px">
        <div class="left_side" style="height:10px; width:350px"><b>Celkový počet študentov:</b></div>
        <div class="count"><?php echo $student_count; ?></div>
    </div>
</div>