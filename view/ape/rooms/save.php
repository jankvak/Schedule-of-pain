<h2>Pridanie miestnosti</h2>
<?php
  // podľa toho ci je nastavene id premetu vieme ci edituje nejaky alebo je pokus o pridat novy
  // nie vsetky udaje budu zadane lebo tento pohlad sa renderuje iba vtedy ak daco zlyha
  
 // if ($room["id"]) echo "<h2>Editácia miestnosti</h2>";
  //else echo "<h2>Pridanie miestnosti</h2>";
  // nastavim premennu, ktora bude aktivna len pri formulari po chybe, treba mi to na checkboxy..
  $chyba = "ano";
  // pouzije defaultnu sablonu ...
  require "add_edit_save_tpl.php";
?>