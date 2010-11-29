<?php 
    // uzitocne funkcie ktore mozu byt pouzivane v pohladoch
    
    /**
     * Funkcia vypise obsah premennej ale len ked je nastavena.
     * Vhodne do pohladov aby sa negenerovali notices o tom
     * ze indexy neexistuju ak sa pridavaju nove zaznamy
     * 
     *  Ak by bola premenna pozuita trebars aj do fb(), bude vygenerovane notice.
     *  V tomto priapde ale k premennej nepristupu iba overi ci existuje a az potom.
     *  
     *  POZOR: ak sa umyselne predava null vrati prazdny retazec, 
     *  musela by sa upravit testovacia podmienka
     * @param $value - hodnota na vypisanie
     */
    function echoParam(&$value)
    {
        echo isset($value) ? $value : "";
    }
?>