<?php
/**
 * Helper trieda pre generovanie konverzii datumov medzi SK formatovanim a
 * internym fomratovanim DATE v PostgreSQL
 * @author matej
 *
 */
class DateConvert
{
    public static function SKtoDB($date)
    {
        return "TO_DATE({$date}, 'DD.MM.YYYY')";
    }

    public static function DBtoSK($stlpec)
    {
        return "TO_CHAR({$stlpec}, 'DD.MM.YYYY')";
    }

    public static function DBTimestampToSkDateTime($stlpec)
    {
        return "TO_CHAR({$stlpec}, 'DD.MM.YYYY HH24:MI')";
    }

    public static function SKDateTime2DBTimestamp($dateTime)
    {
        return "TO_TIMESTAMP({$dateTime}, 'DD.MM.YYYY HH24:MI')";
    }

    //format napr. 'DD.MM.YYYY HH24:MI'
    public static function DBTimestampToDateTimeString($stlpec,$format)
    {
        return "TO_CHAR({$stlpec}, '{$format}')";
    }

    /**
     * Funkcia porovnava dva SK timestamp (formatu dd.mm.yyyy hh:mm)
     * @param $dateTime1 - prvy cas
     * @param $dateTime2 - druhy cas
     * @return boolean - vrati:
     * - zaporne cislo ak $dateTime1 < $dateTime2
     * - 0 ak ak $dateTime1 == $dateTime2
     * - kladne cislo ak $dateTime1 > $dateTime2
     * - null ak jeden z datumov nie je korektny sk datum
     */
    public static function compareSKTimestamp($dateTime1, $dateTime2)
    {
        if (preg_match("/([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4}) ([0-9]{1,2}):([0-9]{1,2})/", $dateTime1, $date1) &&
            preg_match("/([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4}) ([0-9]{1,2}):([0-9]{1,2})/", $dateTime2, $date2))
        {
            $date1 = mktime($date1[4], $date1[5], 0, $date1[2], $date1[1], $date1[3]);
            $date2 = mktime($date2[4], $date2[5], 0, $date2[2], $date2[1], $date2[3]);

            return $date1 - $date2;
        }else return null;
    }
}
?>