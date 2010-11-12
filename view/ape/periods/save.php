<?php
    if (empty($semester["id"])) "<h2>Pridať nový semester</h2>"; 
    else echo "<h2>Upraviť semester</h2>";
    
	include "add_edit_save.php";
?>