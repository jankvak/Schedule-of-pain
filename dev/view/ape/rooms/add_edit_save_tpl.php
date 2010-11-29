<?php 
  // podla toho ci je nastaveny id miestnosti ide o 
  // postnutie noveho formularu / editacia existujuceho zaznamu
  if (isset($room['id'])) $action = "ape/rooms/saveEdited/{$room['id']}";
  else $action = 'ape/rooms/save';
  
?>
<form method="post" action='<?php echo $action; ?>'>
	<input type="hidden" value="<?php echo $room['id']?>" name="id">
	<p>
		<label for="nazov">Názov</label>
		<input type="text" id="nazov" name="nazov" value="<?php echoParam($room['nazov']); ?>"/>
	</p>
	<p>
		<label for="kapacita">Kapacita</label>
		<input type="text" id="kapacita" name="kapacita" value="<?php echoParam($room['kapacita']);?>"/>
	</p>
	<p>
		<label for="poznamka">Poznámka</label>
		<input type="text" id="poznamka" name="poznamka" value="<?php echoParam($room['poznamka']);?>"/>
	</p>
	<p>
		<label for="id_miestnost_typ">Typ</label>
		<select id="id_miestnost_typ" name="id_miestnost_typ">
			<?php
				foreach($room_types as $room_type) {
					if ($room['id_miestnost_typ'] == $room_type['id']) {
						echo "<option selected='selected' value='{$room_type['id']}'>{$room_type['nazov']}</option>";
					}
					else {
						echo "<option value='{$room_type['id']}'>{$room_type['nazov']}</option>";
					}
				}
			?>
		</select>
	</p>
	<?php
	foreach ($equips as $eq){
		$str="<p><label for='vybavenie_{$eq['id']}'>{$eq['typ']}</label><input id='vybavenie_{$eq['id']}' name='vybavenie[]' type='checkbox' value='{$eq['id']}' ";
		// tato kontrola je dolezita, ak nastala chyba a nebolo by zadane ziadne vybavenie, tak by to padlo, tak treba skontrolovat, ostatne formulare to nijak neovplyvni
		if (isset($equip)){
			foreach ($equip as $chk){
				// ak je formular po chybe, tak equip je len pole idciek, ak je to editacia, tak equip je vysledok dotazu z databazy
				if ($chyba == "ano") {
					if ($chk==$eq['id']) $str=$str."checked='checked' ";
				}
				else {
					if ($chk['id_vybavenie']==$eq['id']) $str=$str."checked='checked' ";
				}
				
			}
		}
		$str=$str."/></p>";
		echo $str;
	}
	?>
	<p>
		<input type="submit" value="Ulož"/>
	</p>
</form>
<p>
<a href="ape/rooms/index">Naspäť</a>
</p>