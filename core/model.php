<?php

class Model extends AutoLoadable {
    var $dbh;

  /*
    Pre korekciu vstupných údajov pre metódu bind() sa dajú použiť nasledovné 
    polia pri definícii potomka. Indexom je vždy kontrolovaná premenná, hodnota 
    sú podmienky alebo jej názov vo formulári.
    
    var $check = array(
      "nazov_premennej"  => array(
        "not_empty" => true,
        "maxlength" => 100,
        "popis"     => "názov premennej vo formulári"
        "is_int" => true,
        "min_value" => 0,
        "max_value" => 200,
        "not_equal" => 0,
        "not_equal_hlaska" => "Nebolo vybrané to a to",
        "block_tags" => true
        "is_mail"	=> true
        "is_date"	=> true
        "is_datetime"	=> true
        "array" => array(
            "[key1]" => array(
                -- obmedzenia tak isto ako pre nazov_premennej --
                -- pouzit "array" na tejto urvni je nelegalne, da sa rovno naspecifikovat viacurovnovo, vid nizsie
            ),
            "[key1][key2][key3]" => array(
                -- obmedzenia tak isto ako pre nazov_premennej --
            )
        )
      )
    );
      
    - index "popis" je povinný pre každú kontrolovanú premennú
    - nazadaná inedx znamená, že daná vec sa nebude kontrolovať (min_value, not_empty, ...)
    - not_equal musí byť zadané spoločne s not_equal_hlaska,
      ich účelom je v prípade comboboxu ošetriť či nebola ponechaná default hodnota
      (napr. Vyberte študijný program)
    - block_tags - prekonvertuje string, tak aby sa v \om nevyskytovali HTML tagy 
      (nahradi znaky <,>,.. ich HTML kodmi)
      POUZIVAT IBA ked sa to opatovne pouzije do textarey
    - is_int kontroluje ci bolo zadane cele cislo na baze regexpu
    - is_mail kontroluje ci bola zadana korektna emailova adresa
    - id_date kontroluje ci bol zadany SK datum vo formate DD.MM.YYYY
    - is_datetime kontroluje ci bol zadany datum+cas vo formate DD.MM.YYYY HH:MM
    - array kontroluje zadane elementy v poli (zvlada aj multidimenzionalne)
  */

    function __construct() {
        $this->dbh = Connection::get();
    }
}

?>
