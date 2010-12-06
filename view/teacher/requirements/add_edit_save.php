<script type="text/javascript" src="js/add_tab.js"></script>
<script type="text/javascript" src="js/rem_tab.js"></script>
<script type="text/javascript" src="js/switch_tab.js"></script>
<script type="text/javascript" src="js/add_rem_common.js"></script>
<script type="text/javascript" src="js/add_rem_lecture.js"></script>
<script type="text/javascript" src="js/room_capacity.js"></script>
<script type="text/javascript" src="js/rem_room.js"></script>
<script type="text/javascript" src="js/checkbox.js"></script>
<!-- Apply dropdown check list to the selected items -->
<script type="text/javascript">
    $(document).ready(function() {
        $("#s1").dropdownchecklist({ emptyText: "Prosim vyberte si ..."  });
        $("#s2").dropdownchecklist({ emptyText: "Prosim vyberte si ..."  });
        $("#s3").dropdownchecklist({ emptyText: "Prosim vyberte si ...", firstItemChecksAll: true });
    });
</script>
<script type="text/javascript">
    
    //<![CDATA[
<?php
echo "rooms = {";
$lastcapacity = $rooms[0]["kapacita"];
$roomlist = array();
foreach ($rooms as $room) {
    if ($room["kapacita"] == $lastcapacity) {
        $roomlist[] = '{"name": "' . $room["nazov"] . '", "id": "' . $room["id"] . '"}';
    } else {
        echo '"' . $lastcapacity . '-": [' . implode(',', $roomlist) . '],';
        $roomlist = array('{"name": "' . $room["nazov"] . '", "id": "' . $room["id"] . '"}');
        $lastcapacity = $room["kapacita"];
    }
}
// a co takto na konci vypisat poslednu skupinu ...
echo '"' . $lastcapacity . '-": [' . implode(',', $roomlist) . '],';
echo "}";
?>
    //]]>
</script>
<script type="text/javascript">
    //<![CDATA[
<?php
echo "rooms_selected = {";
if (isset($requirement["layouts"])) {
    foreach ($requirement["layouts"] as $rozl_id => $rozl) {
        foreach ($rozl["requirement"] as $req_id => $req) {
            $id = $req_id . $rozl_id;
            // nemusel nic vybrat, v tomto pripade by foreach generoval warning a pokazil JS
            // preto manualne treba osetrit ci zadal alebo nie
            if (isset($req["rooms"]["selected"])) {
                $sel = "";
                foreach ($req["rooms"]["selected"] as $sel_room)
                    $sel .= "{$sel_room},";
                echo '"' . $id . '" : [' . $sel . '],';
            }
        }
    }
}
echo "}";
?>
    //]]>
</script>

<?php
define("LECTURE_COUNT", 3);

function hasAttr($p, $attr) {
    return $p ? "$attr='$attr'" : "";
}

function reqhtml($id_requirement, $id_layout, $capacities, $roomsByName, $prednaska, &$req, $student_count, $poziadavka_prebrata) {
    $id = $id_requirement . $id_layout;
    $display = ($prednaska == 1 || $req) ? "block" : "none";
    // len ak mame data nebude disabled
    $disabledReq = hasAttr(!$req, "disabled");

    if ($req) {
        //nacitame povodne hodnoty
        $lecture_hours = $req["lecture_hours"];
        $after_lecture = hasAttr($req["after_lecture"], "checked");
        $before_lecture = hasAttr($req["before_lecture"], "checked");
        $comment = $req["comment"];
        $chair_count = $req["equipment"]["chair_count"];
        $notebook = hasAttr($req["equipment"]["notebook"], "checked");
        $beamer = hasAttr($req["equipment"]["beamer"], "checked");

        // ak je poziadavka preberana, alebo ak sa zadava nova, tak $student_count sa pouzije defaultny (parameter funkcie)
        if (!$poziadavka_prebrata) {
            $student_count = $req["rooms"]["students_count"];
        }
    } else {
        //defaultne hodnoty
        $lecture_hours = 2;
        $after_lecture = "";
        $before_lecture = "";
        $comment = "";
        $chair_count = 0;
        $notebook = "";
        $beamer = "";
    }
    // Poznamka: select s miestnostami ma empty option aby nefrflal validator
    // lebo data budu naplnane JS
    $html = '
            <div id="heading' . $id . '" style="display: ' . $display . ';">Prednáška ' . $prednaska . ':</div>
            <div id="lecture' . $id . '" class="color2" style="display: ' . $display . '">
                <div class="row">
                    <div class="left_side">Rozsah prednášky:</div>
                    <div class="right_side"><input size="5" value="' . $lecture_hours . '" name="requirement[layouts][' . $id_layout . '][requirement][' . $id_requirement . '][lecture_hours]" ' . $disabledReq . '/> hodiny</div>
                </div>

            <div class="row">
            <div class="inside_block color3">
					<div class="room_chooser color4">
						Vyhovujúce miestnosti:
                        <select size="4" style="width: 160px;" class="room_list" id="room_list_' . $id . '" name="requirement[layouts][' . $id_layout . '][requirement][' . $id_requirement . '][rooms][selected][]" ' . $disabledReq . ' multiple="multiple">
                        	<option>&nbsp;</option>
						</select>
					</div>
					<div class="row" style="width: 400px;">
						<div class="left_side">Počet študentov:</div>
						<div class="right_side" style="width: 100px;"><input size="5" value="' . $student_count . '" name="requirement[layouts][' . $id_layout . '][requirement][' . $id_requirement . '][rooms][students_count]" ' . $disabledReq . '/></div>
					</div>

					<div class="row" style="width: 400px;">
						<div class="left_side">Vybavenie miestnosti:</div>
						<div class="right_side" style="width: 100px;"><input type="checkbox" style="margin-left: 0px;" name="requirement[layouts][' . $id_layout . '][requirement][' . $id_requirement . '][equipment][notebook]" ' . $disabledReq . ' ' . $notebook . '/> notebook</div>
					</div>
					<div class="row" style="width: 400px;">
						<div class="left_side">Stoličky navyše: <input size="5" value="' . $chair_count . '" name="requirement[layouts][' . $id_layout . '][requirement][' . $id_requirement . '][equipment][chair_count]" ' . $disabledReq . '/></div>
						<div class="right_side" style="width: 100px;"><input type="checkbox" style="margin-left: 0px;" name="requirement[layouts][' . $id_layout . '][requirement][' . $id_requirement . '][equipment][beamer]" ' . $disabledReq . ' ' . $beamer . '/> projektor</div>
					</div>
                                        
                                        <div class="row" style="width: 400px;">
						<div class="right_side">
                                                Vybavenie<br/>
                                                <div class="left_side">
                                                <select id="s2" multiple="multiple">
                                                   <option>Notebook</option>
                                                   <option>Projektor</option>
                                                   <option>Meotar</option>
                                                   <option>Radiator</option>
                                                </select>
                                                </div>

					</div>
                    <div class="row" style="width: 400px;">
						<div class="left_side">Kapacita miestnosti:</div>
						<div class="right_side" style="width: 100px;">
                        <select size="1" class="capacity_teacher" id="capacity_' . $id . '" name="requirement[layouts][' . $id_layout . '][requirement][' . $id_requirement . '][rooms][capacity]" ' . $disabledReq . '>';
    foreach ($capacities as $capacity) {
        $sel = hasAttr($req["rooms"]["capacity"] == $capacity["kapacita"], "selected");
        $html .= '<option ' . $sel . '>' . $capacity["kapacita"] . '</option>';
    }
    $html .= '</select>
                        </div>
					</div>';

    //select na konkretne miestnosti
    $html .= '  <div class="row" style="width: 400px;">
                    <div class="left_side room_select_header" id="room_select_header_' . $id . '" title="Klikni pre výber konkrétnej miestnosti"> Vybrať konkrétnu miestnosť</div>
                    <div class="right_side" style="width: 100px;">
                        <select size="1" class="room_select" id="room_select_' . $id . '" >';
    $html .= '<option value="0"></option>';
    foreach ($roomsByName as $room) {
        $html .= '<option value="' . $room['id'] . '_' . $room['typ_id'] . '_' . $room['kapacita'] . '">' . $room["nazov"] . '</option>';
    }

    $html .= '
                        </select>
                    </div>
                </div>
            </div>
			</div>
			<div class="row">
				<div class="left_side">Špeciálne požiadavky:</div>
				<div class="right_side"></div>
			</div>
			<div class="row">
				<div class="left_side"><input type="checkbox" style="margin-left: 0px;" name="requirement[layouts][' . $id_layout . '][requirement][' . $id_requirement . '][after_lecture]" ' . $disabledReq . ' ' . $after_lecture . '/> cvičenie je hneď po prednáške</div>
				<div class="right_side"><input type="checkbox" style="margin-left: 0px;" name="requirement[layouts][' . $id_layout . '][requirement][' . $id_requirement . '][before_lecture]" ' . $disabledReq . ' ' . $before_lecture . '/> cvičenie nie je skôr ako prednáška</div>
			</div>
			<div class="row">
				<div class="right_side"><textarea rows="3" cols="70" name="requirement[layouts][' . $id_layout . '][requirement][' . $id_requirement . '][comment]" ' . $disabledReq . '>' . $comment . '</textarea></div>
                </div>
                </div>';
    return $html;
}

function generateLayoutHtml($number, $name, $capacities, $roomsByName, &$requirements, $student_count, $poziadavka_prebrata) {
    $disabledLayout = hasAttr($number > 0 && !$requirements, "disabled");
    echo "<div class='part $name color1' ";
    if ($number == 0)
        echo "style='display: block;'"; echo">";
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
							v týždni:
							<select size='1' id='lecture_count_$name' name='requirement[layouts][$name][lecture_count]' {$disabledLayout}>";
    for ($i = 1; $i <= LECTURE_COUNT; $i++) {
        if ($requirements)
            $sel = hasAttr($requirements["lecture_count"] == $i, "selected");
        else
            $sel = hasAttr($i == 1, "selected");
        echo "                                              <option {$sel}>{$i}</option>";
    }

    echo "						</select>
              				<span style='margin-left: 60px'>
              					Všetky?
              					<input type='checkbox' id='checkall_$name' class='checkall' {$disabledLayout}/>
              				</span>
						</div>
						<div class='right_side' style='word-spacing: 3.4px;'>
							<div id='checkarea_$name' class='checkarea'>";
    for ($i = 1; $i <= 13; $i++) {
        $checked = hasAttr($requirements["weeks"][$i - 1], "checked");
        echo "<div class='cbox'><input id='$name$i' type='checkbox' name='requirement[layouts][$name][weeks][" . ($i - 1) . "]' {$disabledLayout} {$checked}/></div>";
    }
    echo "'<div class='left_side'>";
    echo "<select id=\"s3\" multiple=\"multiple\">
                                                   <option>Všetky</option>
                                                   <option>1</option>
                                                   <option>2</option>
                                                   <option>3</option>
                                                   <option>4</option>
                                                   <option>5</option>
                                                   <option>6</option>
                                                   <option>7</option>
                                                   <option>8</option>
                                                   <option>9</option>
                                                   <option>10</option>
                                                   <option>11</option>
                                                   <option>12</option>
                                                   <option>13</option>
                                                </select></div>";
    echo

    "</div>
					  		</div>
						</div>
					  </div>";
    for ($i = 1; $i <= 3; $i++) {

        echo reqhtml("$i", "$name", $capacities, $roomsByName, "$i", $requirements["requirement"][$i], $student_count, $poziadavka_prebrata);
    }
    echo "</div>";
}

function generateTab($number, &$requirements) {
    $class = $number == 1 ? "active" : "passive";
    $add = $remove = ""; // default hodnoty
    $style = ($number == 1 || isset($requirements)) ? "style=\"display: block;\"" : "";
    if ($number <= 2)
        $add = "<div id=\"add{$number}\" class=\"icon\"><img alt=\"Pridať nové rozloženie\" src=\"img/icon_add.jpg\" /></div>\n";
    if ($number > 1)
        $remove = "<div id=\"rem{$number}\" class=\"icon\"><img alt=\"Zrušiť rozloženie\" src=\"img/icon_rem.jpg\" /></div>\n";
    else
        $remove = "";
    echo
    "           <div id=\"tab{$number}\" class=\"{$class}\" {$style}>
                <div id=\"switch{$number}\" class=\"heading\">Rozloženie {$number}</div>
    {$add}{$remove}
            </div>";
}
?>
<h2>Požiadavky prednášajúceho pre predmet: <?php echo $subject['nazov']; ?> </h2>

<?php
include "view/helpers/students_count.php";
require_once "view/helpers/preberanie.php";

if (!isset($requirement))
    zobrazMinulorocnePredmety($minule, $subject, "teacher/requirements/copy/{$course_id}", $blokovanie_preberania);
?>
<form style="margin: 0px;" method="post" action="teacher/requirements/save">

    <div class="row">

        Poznámka k požiadavke:<br />
        <textarea rows="3" style="height:52px;" cols="70" name='requirement[komentare][vseobecne]'><?php echoParam($requirement["komentare"]["vseobecne"]); ?></textarea><br />
    </div>
    <div class="row" style="margin-bottom: 10px;">
        Požiadavka na softvér:<br />
        <select id="s1" multiple="multiple">
            <option>Visual studio</option>
            <option>Rational software architect</option>
            <option>Webshere</option>
            <option>Eclipse</option>
        </select>
        <textarea rows="3" style="height:52px;" cols="70" name='requirement[komentare][sw]'><?php echoParam($requirement["komentare"]["sw"]); ?></textarea><br />
    </div>

    <div id="tabs">
        <div id="handling">
            <?php
            for ($i = 1; $i <= 3; $i++) {
                $name = chr(ord("a") + $i - 1);
                generateTab($i, $requirement["layouts"][$name]);
            }
            ?>
            <div id="confirm">
                <?php
                if ($read_only_semester != true)
                    echo "<input type='submit' value='Ulož'/>";
                ?></div>
        </div>
    </div>

    <input type="hidden" name="course_id" value="<?php echoParam($course_id); ?>"/>
    <input type="hidden" name="previousMetaID" value="<?php echoParam($actualMetaID); ?>" />
    <input type="hidden" name="poziadavka_prebrata" value="<?php echoParam($poziadavka_prebrata); ?>" />

    <div id="mainForm">
        <?php
                fb($requirement, "requirement");
                generateLayoutHtml(0, "a", $capacities, $roomsByName, $requirement["layouts"]["a"], $student_count, $poziadavka_prebrata);
                generateLayoutHtml(1, "b", $capacities, $roomsByName, $requirement["layouts"]["b"], $student_count, $poziadavka_prebrata);
                generateLayoutHtml(2, "c", $capacities, $roomsByName, $requirement["layouts"]["c"], $student_count, $poziadavka_prebrata);
        ?>
            </div>
        </form>
<?php if (!empty($actualMetaID) && !$poziadavka_prebrata) {
?>
                    <form style="margin: 0px;" method="post" action="teacher/requirements/saveComment">
                        <div class="row" style="margin-bottom: 10px;">
        <?php /* ten komentar musi byt tu inac blbo scrolluje */ ?>
                    <a name="komentare"></a>
                    Komentár k diskusii:<br />
                    <textarea rows="3" style="height:52px;" cols="70" name='commentText'></textarea><br />
                    <input type="submit" value="Odošli komentár" />

                </div>

                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>"/>
                <input type="hidden" name="metaID" value="<?php echo $actualMetaID; ?>"/>
            </form>
<?php } ?>

<?php if (!empty($requirement['komentare']['other']) && !$poziadavka_prebrata) {
 ?>
                    <table style="width: 600px">
                        <thead>
                            <tr>
                                <td>Zadal</td>
                                <td>Kedy</td>
                                <td>text</td>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach ($requirement['komentare']['other'] as $komentar) { ?>
                        <tr>
                            <td><?php echo $komentar['zadal']; ?></td>
                            <td><?php echo $komentar['cas_zadania']; ?></td>
                            <td><?php echo nl2br($komentar['text']); ?></td>
                        </tr>
<?php } ?>
                </tbody>
            </table>
<?php } ?>
  