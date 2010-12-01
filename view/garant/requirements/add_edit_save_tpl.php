<h2>Požiadavky garanta pre predmet: <?php echo $predmet['nazov'] ?></h2>
<div class="row">
        <?php 
            require_once "view/helpers/preberanie.php";
			if (!isset($requirements["abbreviation"]) || $requirements["abbreviation"]=="" ||
                    $requirements["lecture_hours"]=="" || $requirements["lecture_hours"]=="")
                   zobrazMinulorocnePredmety($minule, $predmet, "garant/requirements/copy/{$course_id}", $blokovanie_preberania);
        ?>
</div>
<form method="post" action="garant/requirements/save/">
    <input type="hidden" name="id" value="<?php echo $predmet['id']; ?>"/>
        <p>
        	<label for="skratka">Skratka predmetu:</label>
            <input type="text" size="5" style="width: 200px;" id="skratka" name="skratka" value="<?php echo $requirements["abbreviation"];?>" />
        </p>
        <p>
            <label for="prednasajuci">Prednášajúci:</label>
              <select  size="1" style="width: 200px;" id="prednasajuci" name="prednasajuci">
                            <?php
                            echo "<option value='0'>--nie je--</option>";
                            foreach ($lecturers as $lecturer) {
                                $name = $lecturer['last_name']." ".$lecturer['name'].", ".$lecturer['titles_before']." ".$lecturer['titles_after'];
								$selected = $lecturer["id"] == $requirements["prednasajuci"] ? " selected=\"selected\"" : "";
                                echo "<option value='{$lecturer['id']}'{$selected}>{$name}</option>";
                            }
                            ?>
             </select>
        </p>
        <p>
        	<label for="cviciaci">Vedúci cvičení:</label>
                <select id="cviciaci" name="cviciaci"  size="1" style="width: 200px;">
                            <?php
                            echo "<option value='0'>--nie je--</option>";
                            foreach ($teachers as $teacher) {
                                $name = $teacher['last_name']." ".$teacher['name'].", ".$teacher['titles_before']." ".$teacher['titles_after'];
								$selected = $teacher["id"] == $requirements["cviciaci"] ? " selected=\"selected\"" : "";
                                echo "<option value='{$teacher['id']}'{$selected}>{$name}</option>";
                            }
                            ?>
                </select>
        </p>
        <p>
        	<label for="pred_hod">Rozsah prednášok:</label>
            <input size="5" type="text" id="pred_hod" name="pred_hod" value="<?php echo $requirements["lecture_hours"]; ?>"/> hodiny/týždeň;
        </p>
        <p>
        	<label for="cvic_hod">Rozsah cvičení:</label>
            <input size="5" type="text" id="cvic_hod" name="cvic_hod" value="<?php echo $requirements["exercise_hours"]; ?>"/> hodiny/týždeň;
        </p>
    <p>
        <?php
            if($read_only_semester != true)
                echo "<input type='submit' value='Ulož'/>";
        ?>
    </p>
</form>
<p>
    <a href="garant/requirements/index">Naspäť</a>
</p>

