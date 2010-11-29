<script src="js/autocomplete.js" type="text/javascript"></script>
<h2>Pridávanie nového používateľa</h2>

<form method="post" action="administrator/users/save" class="profile">
<fieldset>
<p>
	<label for="username">Používateľské meno:</label>
	<input id="username" name="username" type="text" />
</p>

<?php
	foreach ($groups as $grp){
    if ($grp['id'] < 1) continue;
		echo "<p><label for='skupina_{$grp['id']}'>{$grp['nazov']}</label><input id='skupina_{$grp['id']}' name='skupina[]' type='checkbox' value='{$grp['id']}' /></p>";
	}
?>
<p>
	<input type="submit" value="Pridať" />
</p>
</fieldset>
</form>
<p>
<br\><a href='administrator/users/index'>Naspäť</a>
</p>