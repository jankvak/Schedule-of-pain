<script type="text/javascript" src="js/switch_tab.js"></script>

<h2>Požiadavky na cvičenia pre predmet: <?php echo $subject; ?></h2>
<table style="border-bottom:0px" border="0">
    <tr style="background-color: white;">
        <?php if (isset($previousMetaID)) {?>
        <td style="padding-left:0px"align="left"><a href="scheduler/req_cvicenie/show/<?php echo $previousMetaID; ?>">&lt;&lt;&lt; staršia</a></td>
        <?php }?>
        <?php if (isset($nextMetaID)) {?>
        <td align="right"><a href="scheduler/req_cvicenie/show/<?php echo $nextMetaID; ?>">novšia &gt;&gt;&gt;</a></td>
        <?php }?>
    </tr>
</table>

<div>
    <div class="row">
        <div class="left_side">Zadal:</div>
        <div class="right_side"><?php echo $meta_poziadavka["pedagog"]; ?></div>
    </div>
    <div class="row">
        <div class="left_side">Čas zadania:</div>
        <div class="right_side"><?php echo $meta_poziadavka["cas_pridania"]; ?></div>
    </div>
    <?php 
		include "view/helpers/students_count.php";
	?>    
    <div class="row">
        <div>Poznámka k požiadavke:</div>
        <div><textarea rows="3" readonly style="height:52px;" cols="70" name='requirement[komentare][vseobecne]'><?php echo $requirement["komentare"]["vseobecne"];?></textarea></div>
    </div>
    <div class="row" style="margin-bottom: 10px;">
        <div>Požiadavka na softvér:</div>
        <div><textarea rows="3" readonly style="height:52px;" cols="70" name='requirement[komentare][sw]'><?php echo $requirement["komentare"]["sw"];?></textarea></div>
    </div>
</div>

<?php

// sprav z rooms asociativne pole id->nazov
$rooms_nazvy = array();
foreach ($rooms as $room)
{
	$rooms_nazvy[$room["id"]] = $room["nazov"]; 
}

if(sizeof($requirement) < 1) echo "<p class='error'>Požiadavka na predmet nebola zadaná.</p>";
else {
	
	function hasAttr($p, $attr) {
    	return $p ? "$attr='$attr'" : "";
	}
	
	// vyrenderuje spodnu cast poziadaviek (pocet studentov, typ miestnosti a kapacitu miestnosti)
	function grouphtml($id_requirement, $id_layout, $skupina, $reqSkupina, $rooms_nazvy, $types)
	{
		if (empty($reqSkupina)) return;
		$id = $id_requirement . $id_layout;
		
		$student_count = $reqSkupina['students_count'];
		$capacity = $reqSkupina['capacity'];
		
		// zisti si nazov typu miestnosti podla jeho idcka
		foreach($types as $typ) {
			if ($reqSkupina["type"] == $typ["id"])
				$type = $typ['nazov'];
		}
		
		// zostavi vyselektovane miestnosti na zaklade zoznamu miestnosti
        //najprv vytiahne nazvy a potom spoji
        $sel_rooms = array();
        foreach ($reqSkupina["selected"] as $sel_room)
        {
        	$sel_rooms[] = $rooms_nazvy[$sel_room];
        }
        $sel_rooms = implode(", ", $sel_rooms);
		
		$html = '
			<div id="h_group'.$id.'_'.$skupina.'" style="display: block;" class="h_group">Typ skupiny '.$skupina.':</div>
			<div id="group'.$id.'_'.$skupina.'"  style="display: block; height: 60px;  margin-top: 5px;" class="color3 group">
				<div class="room_chooser color4" style="height: 50px;">
					Vyhovujúce miestnosti:
					<input style="width: 160px;" readonly="readonly" value="'.$sel_rooms.'" />
				</div>
				<div class="row" style="width: 400px;">
					<div class="left_side" style="width: 130px;">Študentov v skupine:</div>
					<div class="right_side" style="width: 50px;"><input size="3" readonly="readonly" value="'.$student_count.'" /></div>
					<div class="left_side" style="width: 130px;">Kapacita miestnosti:</div>
					<div class="right_side" style="width: 50px;"><input size="3" readonly="readonly" value="'.$capacity.'" /></div>
				</div>
				<div class="row" style="width: 400px;">
					<div class="left_side" style="width: 130px;">Typ miestnosti:</div>
					<div class="right_side" style="width: 200px;"><input size="20" readonly="readonly" value="'.$type.'" /></div>
				</div>
			</div>
		';
		return $html;
	}

	// vyrenderuje stred poziadavky rozsah cvicenia, maximalny pocet cviceni sucasne a komentar
	function reqhtml($id_requirement, $id_layout, $cvicenie, $req, $rooms_nazvy, $types)
	{
		if (empty($req)) return;
		$id = $id_requirement . $id_layout;
		
		$pract_hours = $req["pract_hours"];
		$pract_paralell = $req["pract_paralell"];
		$comment = $req["comment"];
		
		$group1 = grouphtml($id_requirement, $id_layout, '1' ,$req['rooms']['1'], $rooms_nazvy, $types);
		$group2 = grouphtml($id_requirement, $id_layout, '2' ,$req['rooms']['2'], $rooms_nazvy, $types);
		
		$html = '
		<div id="heading'.$id.'" style="display: block;">Cvičenie: '.$cvicenie.'</div>
		<div id="lecture'.$id.'" class="color2" style="display: block;">
			<div class="row">
				<div class="left_side">Rozsah cvičenia:</div>
				<div class="right_side"><input size="5" readonly="readonly" value="'.$pract_hours.'" /> hodiny</div>
			</div>
			<div class="row">
				<div class="left_side">Maximálny počet cvičení súčasne:</div>
				<div class="right_side"><input size="5" readonly="readonly" value="'.$pract_paralell.'" /></div>
			</div>
			'.$group1.'
			'.$group2.'
						<div class="row" style="width: 400px;">
								<div class="left_side" style="width: 400px;">Poznámka:</div><br/>
								<div class="right_side" style="width: 400px;"><textarea readonly="readonly" rows="3"  style="height:52px;" cols="70">'.$comment.'</textarea></div>
						</div>
		</div>
		';
		return $html;
	}
	// $number je cislo rozlozenia 0 1 2
	// $name   je meno rozlozenia a b c
	function generateLayoutHtml($number, $name, $req, $rooms_nazvy, $types)
	{
		echo "<div class='part $name color1' "; if ($number == 0) echo "style='display: block;'"; echo">";
		echo "<div class='core_head color2'>
					<div class='row'>
						<div class='left_side'>Počet cvičení</div>
						<div class='right_side' style='word-spacing: 3px;'>
							<div class='cbox'>1.</div>
							<div class='cbox'>2.</div>
							<div class='cbox'>3.</div>
							<div class='cbox'>4.</div>
							<div class='cbox'>5.</div>
							<div class='cbox'>6.</div>
							<div class='cbox'>7.</div>
							<div class='cbox'>8.</div>
							<div class='cbox'>9.</div>
							<div class='cbox'>10.</div>
							<div class='cbox'>11.</div>
							<div class='cbox'>12.</div>
							<div class='cbox'>13.</div>
						</div>
					</div>
					<div class='row'>
						<div class='left_side'>
							v týždni: <input size='1' id='lecture_count_$name' readonly='readonly' value='{$req['pract_count']}' />			
		</div>
						 <div class='right_side' style='word-spacing: 3.4px;'>";
		for ($i=0; $i<=12; $i++) {
			echo "<div class='cbox'>
				<input type='checkbox' id='$name$i' disabled='disabled' style='margin-left: 0px;'"; 
			if($req["weeks"][$i]) echo ' checked="checked"'; 
			echo "/>
			</div>";
		}
		echo "</div>
					</div>
				</div>";

		for ($i=1; $i<=3; $i++) {
			echo reqhtml("$i", "$name", "$i", $req["requirement"][$i], $rooms_nazvy, $types);
		}
		echo "</div>";
	}
	// action je len pro forma
?>
<form id="requirements" style="margin: 0px;" action="#">
    <div id="tabs">
        <div id="handling">
            <div id="tab1" class="active" style="<?php if(sizeof($requirement["layouts"])>=1) { echo "display: block;";}else {echo "";}?>">
                <div id="switch1" class="heading">Rozloženie 1</div>
            </div>
            <div id="tab2" class="passive" style="<?php if(sizeof($requirement["layouts"])>=2) { echo "display: block;";}else {echo "";}?>">
                <div id="switch2" class="heading">Rozloženie 2</div>
            </div>
            <div id="tab3" class="passive" style="<?php if(sizeof($requirement["layouts"])>=3) { echo "display: block;";}else {echo "";}?>">
                <div id="switch3" class="heading">Rozloženie 3</div>
            </div>
        </div>
    </div>

    <div id='mainForm'>
<?php
fb($requirement, "requirement");
generateLayoutHtml(0,"a", $requirement["layouts"]["a"], $rooms_nazvy, $types);
generateLayoutHtml(1,"b", $requirement["layouts"]["b"], $rooms_nazvy, $types);
generateLayoutHtml(2,"c", $requirement["layouts"]["c"], $rooms_nazvy, $types);
?>
    </div>
</form>
<?php
} //koniec poziadavky
?>

<form id="requirements" id="nove" style="margin: 0px;" method="post" action="scheduler/req_cvicenie/saveComment">
    <div class="row" style="margin-bottom: 10px;">
        <div id="left_side">Komentar k diskusii:</div>
        <div id="right_side"><textarea rows="3" style="height:52px;" cols="70" name='commentText'></textarea></div>
        <input type="submit" value="Odošli komentár" />
    </div>
    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>"/>
    <input type="hidden" name="metaID" value="<?php echo $metaPoziadavkaID; ?>"/>
</form>

    <?php if (isset($requirement['komentare']['other'])) {?>
    <table style="width: 600px">
        <thead>
            <tr>
                <td>Zadal</td>
                <td>Kedy</td>
                <td>text</td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requirement['komentare']['other'] as $komentar) {?>
            <tr>
                <td><?php echo $komentar['zadal']; ?></td>
                <td><?php echo $komentar['cas_zadania']; ?></td>
                <td><?php echo nl2br($komentar['text']); ?></td>
            </tr>
            <?php }?>
        </tbody>
    </table>
    <?php }?>
