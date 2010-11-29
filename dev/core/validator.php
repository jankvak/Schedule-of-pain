<?php

/**
 * Trieda oznamujuca ze bola najdena chyba pri validaci vstupnej hodnoty
 */
class InvalidValue extends Exception {

    function  __construct($message, &$value) {
        parent::__construct($message);
        //zrusi vybranu hodnotu (kvoli polu sa to robi tu, aby zrusilo len chybne indexy)
        $value = "";
    }
}

/**
 *
 *
 * Description of Validator
 *
 * @author Matej Krchniak, TP 2009/2010
 *
 */
class Validator extends AutoLoadable {

/**
 * Rekurzivne prehladava pole a ak najde nejaky constraint tak ho skrontroluje
 * @param <array> $a - checked array
 * @param <array> $check - array with constraint do checked array
 * @param <string> $array_prefix - array prefix in traversal ( generated in this manner: [key1][key2][key3]... )
 */
    private static function traverseArray(&$a, &$check, $array_prefix="") {
        foreach ($a as $key => $value) {
        //identifikacia prvku
            $ident = "{$array_prefix}[{$key}]";
            $constraints = $check[$ident];
            // ak sa ma kontrolovat vnutorna premenna
            if (isset($constraints)) {
            // ak zbehne na konci vrati true, ak padne hodi chybu a vyhodi sa controlleru
            //netraversuje dalej to sa hrabe hlbsie len tuna
                self::validate($value, $constraints, $check);
            }
            if (is_array($value)) {
                self::traverseArray($value, $check, $ident);
            }
        }
    }

    /**
     * Vrati ci je v ramci podmienok specifikovana kontrola daneho typu
     * @param <array> $constraints - obmedzenia na hodnotu testovanej premennej
     * @param <String> $typ - typ kontroly
     * @param <type> $default - defaultna hodnota ak v obmedzeniach nebolo specifikovane
     * @return <type> - hodnota kontrolovaneho typu alebo default ak nebola zadana
     */
    private static function getValidation(&$constraints, $typ, $default = false)
    {
        return isset($constraints[$typ]) ? $constraints[$typ] : $default;
    }

    /**
     * Otestuje validnost zadanych vstupov pre dany prvok
     * @param <?> $value - value testovanej premennej
     * @param <array> $constraints - obmedzenia na hodnotu testovanej premennej
     * @return <boolean> - vracia true ak je premenna korektna,
     * inac throw InvavalidValue s textom chyby + zrusi chybnu hodnotu (v pripade pola len chybna)
     */
    private static function validate(&$value, &$constraints) {
        $empty_check        = self::getValidation($constraints, "not_empty");
        $maxlength          = self::getValidation($constraints, "maxlength");
        $popis              = self::getValidation($constraints, "popis");
        $min_value          = self::getValidation($constraints, "min_value");
        $max_value          = self::getValidation($constraints, "max_value");
        $int_check          = self::getValidation($constraints, "is_int");
        $not_equal          = self::getValidation($constraints, "not_equal");
        $not_equal_hlaska   = self::getValidation($constraints, "not_equal_hlaska");
        $block_tags         = self::getValidation($constraints, "block_tags");
        $date_check			= self::getValidation($constraints, "is_date");
        $datetime_check		= self::getValidation($constraints, "is_datetime");
        $mail_check         = self::getValidation($constraints, "is_mail");
        $is_array           = is_array($value);
        $array_constraints  = self::getValidation($constraints, "array");

        if ($block_tags) {
            // tuna iba blokuje tagy => pouzit iba vtedy ak to opatovne pojde do textarey
            $value = htmlspecialchars($value);
        }
        // niekto zadane desatinne cislo pomocou ciarky a nie bodky
        // docasne zablokovane, int check kotroluje na cislo
        //if ($numeric_check) $value = str_replace(',', '.', $value);
        // kontroly
        if ($empty_check && $value=="") {
            throw new InvalidValue("Položka '{$popis}' nesmie byť prázdna.", $value);
        }
        if (is_int($maxlength) && strlen($value)>$maxlength) {
            throw new InvalidValue("Položka '{$popis}' presahuje maximálnu dĺžku {$maxlength} znakov.", $value);
        }
        if ($int_check && !preg_match("/^(\+|-)?[0-9]+$/", $value)) {
            throw new InvalidValue("Položka '{$popis}' musí byť celé číslo.", $value);
        }
        if (is_int($min_value) && $value<$min_value) {
            throw new InvalidValue("Položka '{$popis}' nesmie byť menšia ako {$min_value}.", $value);
        }
        if (is_int($max_value) && $value>$max_value) {
            throw new InvalidValue("Položka '{$popis}' nesmie byť väčšia ako {$max_value}.", $value);
        }
        if (is_int($not_equal) && $value == $not_equal) {
            throw new InvalidValue("{$not_equal_hlaska}", $value);
        }
        if ($date_check) {
        	if (!preg_match("/([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4})/", $value, $matches))
        		throw new InvalidValue("Položka '{$popis}' nie je korektný dátum vo formáte dd.mm.yyyy.", $value);
        	// month, day, year
        	if (!checkdate($matches[2], $matches[1], $matches[3]))
        		throw new InvalidValue("Položka '{$popis}' obsahuje neexistujúci dátum.", $value);
        }
        if ($datetime_check) {
        	if (!preg_match("/([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4}) ([0-9]{1,2}):([0-9]{1,2})/", $value, $matches))
        		throw new InvalidValue("Položka '{$popis}' nie je korektný dátum a čas vo formáte dd.mm.yyyy hh:mm.", $value);
            // month, day, year
        	if (!checkdate($matches[2], $matches[1], $matches[3]))
        		throw new InvalidValue("Položka '{$popis}' obsahuje neexistujúci dátum.", $value);
        	if ($matches[4]<0 || $matches[4] >= 24)
        		throw new InvalidValue("Položka '{$popis}' obsahuje neexistujúcu hodinu.", $value);
        	if ($matches[5]<0 || $matches[5] >= 60)
        		throw new InvalidValue("Položka '{$popis}' obsahuje neexistujúcu minútu.", $value);
        }
        // maily na localhost defaultne povoli
        if ($mail_check && !preg_match("/@localhost$/", $value)) {
        	if (!preg_match("/^[-\'*+=\\.\/0-9?A-Z_~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i", $value))
        		throw new InvalidValue("Položka '{$popis}' nie je korektná e-mailová adresa.", $value);	
        }
        // vnarana kontrola ak sa jedna o pole
        if ($is_array && $array_constraints) {
            self::traverseArray($value, $array_constraints);
        }
        // ziadna chyba
        return true;
    }

    // kontrola vstupných údajov
    public static function validProperty(&$variable, &$value, &$model) {
    // najprv nastavenie internych veci co bude kontrolovat
        $constraints = $model->check[$variable];
        if (isset($constraints)) {
        // vrati to co vravi kontrola
            return self::validate($value, $constraints);
        } else {
        //ak sa nekontroluje nastavi rovno
            return true;
        }
    }

}
?>
