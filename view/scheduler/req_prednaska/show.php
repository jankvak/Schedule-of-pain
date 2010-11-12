<script type="text/javascript" src="js/switch_tab.js"></script>

<h2>Požiadavky na prednášky pre predmet: <?php echo $subject; ?></h2>
<table style="border-bottom:0px" border="0">
    <tr style="background-color: white;">
        <?php if (isset($previousMetaID)) {?>
        <td style="padding-left:0px"align="left"><a href="scheduler/req_prednaska/show/<?php echo $previousMetaID; ?>">&lt;&lt;&lt; staršia</a></td>
        <?php }?>
        <?php if (isset($nextMetaID)) {?>
        <td align="right"><a href="scheduler/req_prednaska/show/<?php echo $nextMetaID; ?>">novšia &gt;&gt;&gt;</a></td>
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
/*echo "<pre>";
var_dump($requirement);
echo "</pre>";*/

/*echo "<pre>";
var_dump($rooms);
echo "</pre>";*/

// sprav z rooms asociativne pole id->nazov
$rooms_nazvy = array();
foreach ($rooms as $room)
{
	$rooms_nazvy[$room["id"]] = $room["nazov"]; 
}

if(sizeof($requirement) < 1) echo "<p class='error'>Požiadavka na predmet nebola zadaná.</p>";
else
{
	// ak daco nepojde so stolickami tak pozri ci toto je id v stoliciek v tabulke vybavenie ....
	define("STOLICKY", 3);

	function hasAttr($p, $attr) {
    	return $p ? "$attr='$attr'" : "";
	}
	
	function reqhtml($id_requirement, $id_layout, $prednaska, $req, $rooms_nazvy) 
	{
		$id = $id_requirement . $id_layout;
		// nekreslit nic ak nemame poziadavku
		if (empty($req)) return;
		//$index = $prednaska - 1;
		
        $lecture_hours = $req["lecture_hours"];
		$notebook_checked = hasAttr($req["equipment"]["notebook"], "checked");
		$projektor_checked = hasAttr($req["equipment"]["beamer"], "checked");
        $chair_count = $req["equipment"]["chair_count"];        
		$hned_po_checked = hasAttr($req["after_lecture"], "checked");
		$skor_ako_checked = hasAttr($req["before_lecture"], "checked");
        $comment = $req["comment"];
        $student_count = $req["rooms"]["students_count"];
        $rooms_capacity = $req["rooms"]["capacity"];
        
        // zostavi vyselektovane miestnosti na zaklade zoznamu miestnosti
        //najprv vytiahne nazvy a potom spoji
        $sel_rooms = array();
        foreach ($req["rooms"]["selected"] as $sel_room)
        {
        	$sel_rooms[] = $rooms_nazvy[$sel_room];
        }
        $sel_rooms = implode(", ", $sel_rooms);
		
		$html = '
			<div id="heading'.$id.'" style="display: block;">Prednáška '.$prednaska.':</div>
			<div id="lecture'.$id.'" class="color2" style="display: block;">
				<div class="row">
					<div class="left_side">Rozsah prednášky:</div>
					<div class="right_side"><input size="5" readonly="readonly" value="'.$lecture_hours.'" /> hodiny</div>
				</div>
					<div class="row">
						<div class="inside_block color3" style="height: 60px;">
							<div class="room_chooser color4" style="height: 50px;">
								Vyhovujúce miestnosti:
								<input type="text" readonly="readonly" style="width: 160px;" value="'.$sel_rooms.'" />
							</div>
							<div class="row" style="width: 400px;">
								<div class="left_side" style="width: 140px;">Počet študentov:</div>
								<div class="right_side" style="width: 50px;"><input size="5" readonly="readonly" value="'.$student_count.'" /></div>
								<div class="left_side" style="width: 115px;">Stoličky navyše:</div>
								<div class="right_side" style="width: 50px;"><input size="5" readonly="readonly" value="'.$chair_count.'" /></div>						
							</div>
							<div class="row" style="width: 400px;">
								<div class="left_side" style="width: 140px;">Kapacita miestnosti:</div>
								<div class="right_side" style="width: 50px;"><input size="5" readonly="readonly" value="'.$rooms_capacity.'" /></div>
								<div class="left_side" style="width: 80px;"><input id="chbNote" type="checkbox"  disabled="disabled" style="margin-left: 0px;" '.$notebook_checked.' />notebook</div>
								<div class="right_side" style="width: 80px;"><input id="chbProj" type="checkbox" disabled="disabled" style="margin-left: 0px;" '.$projektor_checked.' />projektor</div>												
							</div>
						</div>
					</div>
					<div class="row">
					<div class="left_side"><input type="checkbox" disabled="disabled" style="margin-left: 0px;" '.$hned_po_checked.' /> cvičenie je hneď po prednáške</div>
					<div class="right_side"><input type="checkbox" disabled="disabled" style="margin-left: 0px;" '.$skor_ako_checked.' /> cvičenie nie je skôr ako prednáška</div>
				</div>
				<div class="row">
					<div class="right_side"><textarea readonly="readonly" rows="3" cols="70">'.$comment.'</textarea></div>
				</div>
			</div>
			';
		return $html;
	}

	function generateLayoutHtml($number, $name, $req, $rooms_nazvy)
	{
		echo "<div class='part $name color1'"; if ($number == 0) echo " style='display: block;'"; echo">";
		echo "<div class='core_head color2'>
					<div class='row'>
						<div class='left_side'>Počet prednášok</div>
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
							v týždni: <input size='1' id='lecture_count_$name' readonly='readonly' value='"; 
		echo "{$req["lecture_count"]}'/>";
		echo "</div>
				        <div class='right_side' style='word-spacing: 3.4px;'>";
		for ($i=0; $i<=12; $i++) {
			echo "<div class='cbox'><input id='$name$i' type='checkbox' disabled='disabled' style='margin-left: 0px;'"; 
			if($req["weeks"][$i]) echo ' checked="checked"'; 
			echo " /></div>";
		}
		echo "</div>
					</div>
		         </div>";
		for ($i=1; $i<=3; $i++) {
			echo reqhtml("$i", "$name", "$i", $req["requirement"][$i], $rooms_nazvy);
		}
		echo "</div>";

	}
	?>

<form id="requirements" style="margin: 0px;" action="#">
<div id="tabs">
<div id="handling">
<div id="tab1" class="active"  style="<?php if(sizeof($requirement["layouts"])>=1) { echo "display: block;";}else {echo "";}?>" >
<div id="switch1" class="heading">Rozloženie 1</div>
</div>
<div id="tab2" class="passive"  style="<?php if(sizeof($requirement["layouts"])>=2) { echo "display: block;";}else {echo "";}?>" >
<div id="switch2" class="heading">Rozloženie 2</div>
</div>
<div id="tab3" class="passive"  style="<?php if(sizeof($requirement["layouts"])>=3) { echo "display: block;";}else {echo "";}?>" >
<div id="switch3" class="heading">Rozloženie 3</div>
</div>
</div>
</div>

<div id='mainForm'>
<?php
fb($requirement,"requirement");
generateLayoutHtml(0, "a", $requirement["layouts"]["a"], $rooms_nazvy);
generateLayoutHtml(1, "b", $requirement["layouts"]["b"], $rooms_nazvy);
generateLayoutHtml(2, "c", $requirement["layouts"]["c"], $rooms_nazvy);
?>
</div>
</form>
<?php
}//koniec textu poziadavky
?>

<form id="requirements" id="nove" style="margin: 0px;" method="post" action="scheduler/req_prednaska/saveComment">
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
