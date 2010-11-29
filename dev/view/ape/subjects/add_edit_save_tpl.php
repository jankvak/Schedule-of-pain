<?php 
  // podla toho ci je nastaveny id predmetu ide o 
  // postnutie noveho formularu / editacia existujuceho znaznamu
  if ($predmet['id']) $action = "ape/subjects/saveEdited/{$predmet['id']}";
  else $action = 'ape/subjects/save';
?>
<form method='post' action='<?php echo $action; ?>'>	
  <input type="hidden" name="id_semester" value="<?php echo $predmet["id_semester"]; ?>"/>
	<p>
		<label for="nazov">Názov</label>
		<?php echo "<input type='text' id='nazov' name='nazov' value='{$predmet['nazov']}'/>";?>
	</p>
	<p>
		<label for="kod">Kód</label>
		<?php echo "<input type='text' id='kod' name='kod' value='{$predmet['kod']}'/>";?>
	</p>
	<p>
		<label for="semester">Semester</label>
		<select id="semester" name="semester"/>
			<?php
				// to ze tuna ifuje ktory selected pri novom nevadi ...
				// $predmet['semester'] nie je nastaveny takze to nikdy nebude true
        for ($i=1;$i<=6;$i++){
					if ($i==$predmet['semester']) echo "<option value='$i' selected='selected'>$i</option>";
					else echo "<option value='$i'>$i</option>";
				}
			?>
		</select>
	</p>
	<p>
		<label for="studijny_program">Študijný program</label>
		<select id="program" name="studijny_program"/>
		<option value="0">Vyberte študijný program</option>
		<?php
			foreach ($programy as $pr){
				if ($pr['id']==$predmet['studijny_program']) echo "<option value='{$pr['id']}' selected='selected'>{$pr['nazov']}</option>";
				else echo "<option value='{$pr['id']}'>{$pr['nazov']}</option>";
			}
		?>
		</select>
	</p>
	<p>
		<label for="sposob_ukoncenia">Spôsob ukončenia</label>
		<select id="ukoncenie" name="sposob_ukoncenia"/>
		<option value="0">Vyberte spôsob ukončenia</option>
		<?php
			foreach ($ukoncenia as $uk){
				if ($uk['id']==$predmet['sposob_ukoncenia']) echo "<option value='{$uk['id']}' selected='selected'>{$uk['nazov']}</option>";
				else echo "<option value='{$uk['id']}'>{$uk['nazov']}</option>";
			}
		?>
		</select>
	</p>
	<p>
		<input type="submit" value="Ulož"/>
	</p>
</form>
<a href="ape/subjects/index">Naspäť</a>