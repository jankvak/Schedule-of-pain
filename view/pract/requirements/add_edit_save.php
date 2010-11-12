<?php
    /* Poznamky:
     *  - vsetky funkcie preberajuce poziadavky dostavaju referenciu na pole
     * (nehlasi tak notices a je to cistejsie na predavanie nech sa nekopiruje)
     */
?>
<script type="text/javascript" src="js/add_tab.js"></script>
<script type="text/javascript" src="js/rem_tab.js"></script>
<script type="text/javascript" src="js/switch_tab.js"></script>
<script type="text/javascript" src="js/add_rem_common.js"></script>
<script type="text/javascript" src="js/add_rem_pract.js"></script>

<script type="text/javascript" src="js/add_group.js"></script>
<script type="text/javascript" src="js/rem_group.js"></script>

<script type="text/javascript" src="js/room_capacity.js"></script>
<script type="text/javascript" src="js/rem_room.js"></script>
<script type="text/javascript" src="js/checkbox.js"></script>

<script type="text/javascript">
    //<![CDATA[
    rooms = {
<?php
$lastcapacity = $rooms[0]["kapacita"];
$lasttype = $rooms[0]["typ_id"];

$roomlist = array();
foreach($rooms as $room) {
    if($room["kapacita"] == $lastcapacity && $room["typ_id"] == $lasttype) {
        $roomlist[] = '{"name": "' . $room["nazov"] . '", "id": "' . $room["id"] . '"}';
    } else {
        echo '"' . $lastcapacity . '-' . $lasttype . '": [' . implode(',', $roomlist) . '],';
        $roomlist = array('{"name": "' . $room["nazov"] . '", "id": "' . $room["id"] . '"}');
        $lastcapacity = $room["kapacita"];
        $lasttype = $room["typ_id"];
    }
}
echo '"' . $lastcapacity . '-' . $lasttype . '": [' . implode(',', $roomlist) . ']';
?>
            }
            //]]>
</script>
<script type="text/javascript">
    //<![CDATA[
<?php
echo "rooms_selected = {";
if (isset($requirement["layouts"])) {
    foreach ($requirement["layouts"] as $rozl_id => $rozl) {
        foreach ($rozl["requirement"] as $req_id => $req) {
            $skupina = 1;
            $id = $req_id.$rozl_id.$skupina;

            if (isset($req["rooms"][$skupina]["selected"])) {
                $sel = "";
                foreach ($req["rooms"][$skupina]["selected"] as $sel_room) $sel .= "{$sel_room},";
                echo '"'.$id.'" : ['.$sel.'],';
            }
            $skupina = 2;
            $id = $req_id.$rozl_id.$skupina;

            if (isset($req["rooms"][$skupina]["selected"])) {
                $sel = "";
                foreach ($req["rooms"][$skupina]["selected"] as $sel_room) $sel .= "{$sel_room},";
                echo '"'.$id.'" : ['.$sel.'],';
            }
        }
    }
}
echo "}";
?>
    //]]>
</script>
<script type="text/javascript">
    //<![CDATA[
<?php
echo "types_capacities = {";
$lasttype = $type_capacity[0]['id'];
$kap="";
foreach ($type_capacity as $typ_cap) {
    if ($lasttype != $typ_cap['id']) {
        echo '"'.$lasttype.'": ['.$kap.'],';
        $lasttype = $typ_cap['id'];
        $kap="{$typ_cap['kapacita']},";
    }
    else {
        $kap .= "{$typ_cap['kapacita']},";
    }
}
// nezabudnut posledne pridat
echo '"'.$lasttype.'": ['.$kap.'],';
echo "}";
?>
    //]]>
</script>

<?php

define("PRACT_COUNT", 3);

function hasAttr($p, $attr) {
    return $p ? "$attr='$attr'" : "";
}

function grouphtml($id_requirement, $id_layout, $capacities, $types, $roomsByName, $skupina, &$reqSkupina, $student_count, $poziadavka_prebrata) {
    $id = $id_requirement . $id_layout;

    $display =  ($skupina == 1 || $reqSkupina) ? "block" : "none";
    $disabledSkupina = hasAttr(!$reqSkupina, "disabled");

    // ak neni zadany pocet studentov v skupine alebo preberame poziadavku a v nej je nejake vacsie cislo ako 20
    // (vacsinou byva na cvikach do 20 ludi alebo potom vsetci)
    // tak chceme aby sa nastavil pocet na pocet studentov v predmete 
    
    if ($reqSkupina && (!$poziadavka_prebrata || ($reqSkupina['students_count'] <= 20)) )
    {
        $student_count = $reqSkupina['students_count'];
    }
  
    // Poznamka: select s miestnostami ma empty option aby nefrflal validator
    // lebo data budu naplnane JS
    $html = '
			<div id="h_group'.$id.'_'.$skupina.'" style="display: '.$display.';" class="h_group">Typ skupiny '.$skupina.':</div>
			<div id="group'.$id.'_'.$skupina.'"  style="display: '.$display.'" class="color3 group">
				<div class="room_chooser color4">
					Vyhovujúce miestnosti:
                    <select size="4" style="width: 160px;" class="room_list" id="room_list_'.$id.$skupina.'" multiple="multiple" name="requirement[layouts]['.$id_layout.'][requirement]['.$id_requirement.'][rooms]['.$skupina.'][selected][]" '.$disabledSkupina.'>
                    	<option>&nbsp;</option>
					</select>
				</div>
				<div class="row" style="width: 400px;">
					<div class="left_side">Študentov v skupine:</div>
                    <div class="right_side" style="width: 100px;"><input size="5" value="'.$student_count.'" name="requirement[layouts]['.$id_layout.'][requirement]['.$id_requirement.'][rooms]['.$skupina.'][students_count]" '.$disabledSkupina.'/></div>
					<div class="add_rem_group_button">'.($skupina == '1' ? '<img id="add_group_'.$id.'" src="img/icon_add_group.jpg" alt="Pridaj druhý typ skupiny" />' : '<img id="rem_group_'.$id.'" src="img/icon_rem_group.jpg" alt="Zruš druhý typ skupiny" />').'</div>
				</div>
				<div class="row" style="width: 400px;">			
                    <div class="left_side">Typ miestnosti:</div>
					<div class="right_side" style="width: 100px;">
						<select size="1" class="type" id="type_'.$id.$skupina.'" style="width: 140px;" name="requirement[layouts]['.$id_layout.'][requirement]['.$id_requirement.'][rooms]['.$skupina.'][type]" '.$disabledSkupina.'>';
    foreach($types as $type) {
        $sel = hasAttr($reqSkupina["type"] == $type["id"], "selected");
        $html .= '<option value="' . $type["id"] . '" '.$sel.'>' . $type["nazov"] . '</option>';
    }

    $html .= '
						</select>						
					</div>
				</div>
				<div class="row" style="width: 400px;">
					<div class="left_side">Kapacita miestnosti:</div>
					<div class="right_side" style="width: 100px;">
						<select size="1" class="capacity_pract" id="capacity_'.$id.$skupina.'" name="requirement[layouts]['.$id_layout.'][requirement]['.$id_requirement.'][rooms]['.$skupina.'][capacity]" '.$disabledSkupina.'>';
    foreach($capacities as $capacity) {
        $sel = hasAttr($reqSkupina["capacity"] == $capacity["kapacita"], "selected");
        $html .= '<option '.$sel.'>'. $capacity["kapacita"] .'</option>';
    }

    $html .= '
						</select>		
					</div>
				</div>';
	 
        //select na konkretne miestnosti
        $html .= '<div class="row" style="width: 400px;">
					<div class="left_side room_select_header" id="room_select_header_'.$id.$skupina.'" title="Klikni pre výber konkrétnej miestnosti"> Vybrať konkrétnu miestnosť</div>
                    <div class="right_side" style="width: 100px;">
                        <select size="1" class="room_select" id="room_select_'.$id.$skupina.'" >';
                        $html .= '<option value="0"></option>';
    foreach($roomsByName as $room) {           
        $html .= '<option value="'.$room['id'].'_'.$room['typ_id'].'_'.$room['kapacita'].'">'. $room["nazov"] .'</option>';
    }
    
    $html .= '
                        </select>
                    </div>
                  </div>
            </div>
		';
    return $html;
}

function reqhtml($id_requirement, $id_layout, $capacities, $types,  $roomsByName, $cvicenie, &$req, $student_count, $poziadavka_prebrata) {
    $id = $id_requirement . $id_layout;
    $display =  ($cvicenie == 1 || $req) ? "block" : "none";
    $disabledReq = hasAttr(!$req, "disabled");

    if ($req) {
        //nacitame povodne hodnoty
        $pract_hours = $req["pract_hours"];
        $pract_paralell = $req["pract_paralell"];
        $comment = $req["comment"];
        $notebook = hasAttr($req["equipment"]["notebook"], "checked");
        $beamer = hasAttr($req["equipment"]["beamer"], "checked");
    }
    else {
    //defaultne hodnoty
        $pract_hours = 2;
        $pract_paralell = 2;
        $comment = "";
        $notebook = "";
        $beamer = "";
    }

    $group1 = grouphtml($id_requirement, $id_layout, $capacities, $types, $roomsByName, '1', $req['rooms']['1'], $student_count, $poziadavka_prebrata);
    $group2 = grouphtml($id_requirement, $id_layout, $capacities, $types, $roomsByName, '2', $req['rooms']['2'], $student_count, $poziadavka_prebrata);


    $html = '
		<div id="heading'.$id.'" style="display: '.$display.';">Cvičenie: '.$cvicenie.'</div>
		<div id="lecture'.$id.'" class="color2" style="display: '.$display.';">
			<div class="row">
				<div class="left_side">Rozsah cvičenia:</div>
                <div class="right_side"><input size="5" value="'.$pract_hours.'" class="common_input" name="requirement[layouts]['.$id_layout.'][requirement]['.$id_requirement.'][pract_hours]" '.$disabledReq.'/> hodiny</div>
			</div>
			<div class="row">
				<div class="left_side">Maximálny počet cvičení súčasne:</div>
                <div class="right_side"><input size="5" value="'.$pract_paralell.'" class="common_input" name="requirement[layouts]['.$id_layout.'][requirement]['.$id_requirement.'][pract_paralell]" '.$disabledReq.'/></div>
			</div>
			<div class="row">
				<div class="left_side">Vybavenie miestnosti:</div>								
				<div class="right_side">
					<input type="checkbox" style="margin-left: 0px;" class="common_input" name="requirement[layouts]['.$id_layout.'][requirement]['.$id_requirement.'][equipment][notebook]" '.$disabledReq.' '.$notebook.'/> notebook
					<div style="width:190px;float:right;">
					<input type="checkbox" style="margin-left: 0px;" class="common_input" name="requirement[layouts]['.$id_layout.'][requirement]['.$id_requirement.'][equipment][beamer]" '.$disabledReq.' '.$beamer.'/> projektor
					</div>
				</div>
			</div>               
			'.$group1.'
			'.$group2.'
                <div class="row">
                        <div class="left_side">Poznámka:</div><br/>
                        <div class="right_side"><textarea rows="3" style="height:52px;" cols="70" name="requirement[layouts]['.$id_layout.'][requirement]['.$id_requirement.'][comment]" '.$disabledReq.'>'.$comment.'</textarea></div>
                </div>
             </div>
		';
    return $html;
}

function generateLayoutHtml($number, $name, $capacities, $types, $roomsByName, &$requirements, $student_count, $poziadavka_prebrata) {
    $disabledLayout =  hasAttr($number > 0 && !$requirements, "disabled");
    echo "<div class='part $name color1' "; if ($number == 0) echo "style='display: block;'"; echo">";
    echo "<div class='core_head color2'>
				<div class='row'>
					<div class=\"left_side\">Počet cvičení</div>
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
					<div class=\"left_side\">
						v týždni:
						<select size='1' id='lecture_count_$name' name='requirement[layouts][$name][pract_count]' {$disabledLayout}>";
    // pri selecte je idcko lecture... a nie pract, ale javascripty pracuju s idckom lecture... trosku matuce
    for ($i=1;$i<=PRACT_COUNT;$i++) {
        if ($requirements) $sel = hasAttr($requirements["pract_count"] == $i, "selected");
        else $sel = hasAttr($i == 1, "selected");
        echo "<option {$sel}>{$i}</option>";
    }

    echo             	"</select>
						<span style='margin-left: 60px'>
							Všetky?
							<input type='checkbox' id='checkall_$name' class='checkall' {$disabledLayout}/>
						</span>				
					</div>
					<div class='right_side' style='word-spacing: 3.4px;'>
						<div class='checkarea' id='checkarea_$name'>";
    for ($i=1; $i<=13; $i++) {
        $checked = hasAttr($requirements["weeks"][$i-1], "checked");
        echo "<div class='cbox'><input id='$name$i' style='width:18px;float: left;text-align: left;' type='checkbox' name='requirement[layouts][$name][weeks][".($i-1)."]' {$disabledLayout} {$checked} /></div>";
    }
    echo "	            </div>
		            </div>
	               </div>
     </div>";
    for ($i=1; $i<=3; $i++) {
        echo reqhtml("$i", "$name", $capacities, $types, $roomsByName, "$i", $requirements["requirement"][$i], $student_count, $poziadavka_prebrata);
    }
    echo "</div>";
}


function generateTab($number, &$requirements) {
    $class = $number == 1 ? "active" : "passive";
    $add = $remove = ""; // default
    $style = ($number == 1 || $requirements) ? "style=\"display: block;\"" : "";
    if ($number <= 2) $add = "<div id=\"add{$number}\" class=\"icon\"><img alt=\"Pridať nové rozloženie\" src=\"img/icon_add.jpg\" /></div>\n";
    if ($number > 1) $remove = "<div id=\"rem{$number}\" class=\"icon\"><img alt=\"Zrušiť rozloženie\" src=\"img/icon_rem.jpg\" /></div>\n";
    else $remove = "";
    echo
    "           <div id=\"tab{$number}\" class=\"{$class}\" {$style}>
				<div id=\"switch{$number}\" class=\"heading\">Rozloženie {$number}</div>
    {$add}{$remove}
			</div>";
}
?>
<h2>Požiadavky cvičiaceho pre predmet: <?php echo $subject['nazov'];?></h2>
<?php 
	include "view/helpers/students_count.php";
	require_once "view/helpers/preberanie.php";
		
	if (!isset($requirement))
		zobrazMinulorocnePredmety($minule, $subject, "pract/requirements/copy/{$course_id}", $blokovanie_preberania);
?>                                                    
<form style="margin: 0px;" method="post" action="pract/requirements/save">
    <div class="row">
        Poznámka k požiadavke:<br />
        <textarea rows="3" style="height:52px;" cols="70" name='requirement[komentare][vseobecne]'><?php echoParam($requirement["komentare"]["vseobecne"]);?></textarea><br />
    </div>
    <div class="row" style="margin-bottom: 10px;">
        Požiadavka na softvér:<br />
        <textarea rows="3" style="height:52px;" cols="70" name='requirement[komentare][sw]'><?php echoParam($requirement["komentare"]["sw"]);?></textarea><br />
    </div>
    <div id="tabs">
        <div id="handling">
            <?php
            for ($i=1;$i<=3;$i++) {
                $name = chr(ord("a")+$i-1);
                generateTab($i, $requirement["layouts"][$name]);
            }
            ?>
            <div id="confirm">
                <?php
                if($read_only_semester != true)
                    echo "<input type='submit' value='Ulož'/>";
                ?> </div>
        </div>
    </div>

    <input type="hidden" name="course_id" value="<?php echoParam($course_id); ?>" />
    <input type="hidden" name="previousMetaID" value="<?php echoParam($actualMetaID); ?>" />
	<input type="hidden" name="poziadavka_prebrata" value="<?php echoParam($poziadavka_prebrata); ?>" />
    <div id="mainForm">
        <?php
        fb($requirement,"requirement");
        generateLayoutHtml(0, "a", $capacities, $types, $roomsByName, $requirement["layouts"]["a"], $student_count, $poziadavka_prebrata);
        generateLayoutHtml(1, "b", $capacities, $types, $roomsByName, $requirement["layouts"]["b"], $student_count, $poziadavka_prebrata);
        generateLayoutHtml(2, "c", $capacities, $types, $roomsByName, $requirement["layouts"]["c"], $student_count, $poziadavka_prebrata);
        ?>
    </div>
</form>
<?php if (!empty($actualMetaID) && !$poziadavka_prebrata) {?>
<form style="margin: 0px;" method="post" action="pract/requirements/saveComment">
    <div class="row" style="margin-bottom: 10px;">
        <?php /* ten komentar musi byt tu inac blbo scrolluje */ ?>
        <a name="komentare"></a>
        Komentár k diskusii:<br/>
        <textarea rows="3" style="height:52px;" cols="70" name='commentText'></textarea><br />
        <input type="submit" value="Odošli komentár" />
    </div>
    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>"/>
    <input type="hidden" name="metaID" value="<?php echo $actualMetaID; ?>"/>
</form>
<?php }?>

<?php if (!empty($requirement['komentare']['other']) && !$poziadavka_prebrata) {?>
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
