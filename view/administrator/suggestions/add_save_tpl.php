<?php
// podla toho ci je nastaveny id pripomienky ide o
// postnutie noveho formularu / editacia existujuceho znaznamu
//admin len upravuje stav, pripadne typ pripomienky, pripomienka sa pridava cez all
//if ($suggestion['id'])
$action = "administrator/suggestions/saveEdited/{$suggestion['id']}";
//else
//  $action = 'administrator/suggestions/save';
?>

<form method='post' action='<?php echo $action; ?>'>
    <p>
        <input type="hidden" name="id" value="<?php echo $suggestion['id']; ?>"/>
        <!-- mozme zmenit stav pripomienky -->
        <label for="stavSelect">Stav:</label>
        <select id="stavSelect" name="stav">
            <?php
            foreach($nazvyStavov as $nazovStavu)
            {
                $idStavu = array_keys($nazvyStavov,$nazovStavu);
                echo "<option value='$idStavu[0]'";
                if ($suggestion["stav"] == $idStavu[0])
                    echo " selected = 'selected'";
                echo ">$nazovStavu</option>";
            }
            ?>
        </select>
    </p>
    <p>
        <!-- Typ pripomienky mozme tiez zmenit, ak pouzivatel zle zadal -->
        <label for="typSelect">Typ:</label>
        <select id="typSelect" name="pripomienka_typ_id">
            <?php
            foreach($suggestion_types as $suggestion_type)
            {
                echo "<option value='{$suggestion_type['id']}'";
                if ($suggestion['pripomienka_typ_id']==$suggestion_type['id'])
                    echo " selected='selected'";
                echo ">{$suggestion_type['nazov']}</option>";
            }
            ?>
        </select>
    </p>  

    <p>Čas pridania: <?php echoParam(date("d.m.Y H:i", $suggestion["casova_peciatka"])); ?></p>

    <p>Pridal: <?php echoParam($suggestion["pedagog_meno"]); ?></p>

    <p>
        <label for="text">Text:</label>
        <textarea name="text" cols="65" rows="15"><?php echoParam($suggestion['text']);?></textarea>
    </p>
    <p>
        <input type="submit" value="Ulož"/><br>
    </p>
</form>