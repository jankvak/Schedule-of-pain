<?php
  // podla toho ci je nastaveny id predmetu ide o 
  // postnutie noveho formularu / editacia existujuceho znaznamu
  if ($equipment['id']) $action = "ape/equipment/update/{$equipment['id']}";
  else $action = 'ape/equipment/save';
?>

<form action='<?php echo $action; ?>' method="post" >
  <fieldset>
       <?php echo "<input type='hidden' id='id' name='id' value='{$equipment['id']}'/>";?>
    <p>
      <label for="typ">Typ</label>
      <?php echo "<input type='text' id='typ' name='typ' value='{$equipment['typ']}'/>";?>
    </p>
    <p>
      <label for="prenosne">Prenosné</label>
      <input type="checkbox" id="prenosne" name="prenosne" <?php $equipment['prenosne'] ? print_r('checked') : print_r('');  ?> />
    </p>
    <p>
      <label for="poznamka">Poznámka</label>
      <?php echo "<input type='text' id='poznamka' name='poznamka' value='{$equipment['poznamka']}'/>";?>
    </p>
    <p>
      <input type="submit" value="Ulož"/>
    </p>
  </fieldset>
</form>
<a href="ape/equipment/index">Naspäť</a>

