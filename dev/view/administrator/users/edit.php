<h2>Úprava používateľa</h2>
<form method="post" action="administrator/users/submitEdited"><?php
echo "<input id='id' name='id' type='hidden' value='$id'/>";
echo "<p><b>Používateľské meno : </b>{$login}</p>";
?> <?php
foreach ($groups as $grp){
	if ($grp['id']<1) continue;
	$str="<p><label for='skupina_{$grp['id']}'>{$grp['nazov']}</label><input id='skupina_{$grp['id']}' name='skupina[]' type='checkbox' value='{$grp['id']}' ";
	foreach ($checked as $chk){
		if ($chk['id_skupina']==$grp['id']) $str=$str."checked='checked' ";
	}
	$str=$str."/></p>";
	echo $str;
}
?>
<p><input type="submit" value="Zmeniť" /></p>
</form>
<p><a href="administrator/users/index">Naspäť</a></p>
