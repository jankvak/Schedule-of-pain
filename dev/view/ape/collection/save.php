<?php 
    if (empty($akcia["id"])) echo "<h2>Pridať rozvrhovú akciu</h2>";
    else echo "<h2>Úprava rozvhovej akcie</h2>";    

	include "add_edit_tpl.php";
?>