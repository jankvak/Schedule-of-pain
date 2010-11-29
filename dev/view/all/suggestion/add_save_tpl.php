<?php
// podla toho ci je nastaveny id pripomienky ide o
// postnutie noveho formularu / editacia existujuceho znaznamu
//zatial sa pripomienky mozu len pridavat
//if ($pripomienka['id'])
//  $action = "all/suggestion/saveEdited/{$pripomienka['id']}";
//else
$action = 'all/suggestion/save';
?>
<form method='post' action='<?php echo $action; ?>'>
    <p>
        <label for="typ">Typ:</label>
        <select id="typ" name="pripomienka_typ_id">
            <option value="0">-- Vyberte typ pripomienky --</option>
            <?php
            foreach($suggestion_types as $suggestion_type)
            {
                echo "<option value='{$suggestion_type['id']}'";
                if (isset($suggestion) && $suggestion['pripomienka_typ_id']==$suggestion_type['id'])
                    echo " selected='selected'";
                echo ">{$suggestion_type['nazov']}</option>";
            }
            ?>
        </select>
    </p>
    <p>
        <label for="text">Text:</label>
        <textarea id="text" name="text" cols="65" rows="15"><?php echoParam($suggestion['text']);?></textarea>
    </p>
    <p>
        <input type="submit" value="Ulož"/>
    </p>
</form>