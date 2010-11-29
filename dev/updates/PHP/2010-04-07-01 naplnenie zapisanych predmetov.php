<?php
/*
 * Vychadzam z prvotneho skriptu, ktory bol ale rozsireny o nasledovne:
 * - studentov uz nemoze len pridavat ale musi zistit jeho id, resp. pridat
 */

function convertEncoding(&$array)
{
    foreach ($array as &$hodnota)
        $hodnota = iconv("cp1250", "UTF-8", $hodnota);
}

define("FILENAME", "updates/PHP/2009-2010ZS-studenti.csv");

define("AIS_ID", 0);
define("MENO", 2);
define("PRIEZVISKO", 1);
define("SEMESTER", 1); // ZS 2009/2010
// tieto udaje bohuzial v tabulke nie su => nebude to presne ...
// nastastie mame udaje z LS nahodene takze 99,999999% studentov uz v DB budu vsetci
// define("STUD_PROGRAM", 1);
// define("ROCNIK", 1);

$dbh = Connection::get();
$dbh->TransactionBegin();

if (!file_exists(FILENAME))
{
    throw new Exception("Vstupny subor {FILENAME} nenajdeny");
}

$zaznamy = file(FILENAME);
// dropni prve dva riadky
unset($zaznamy[0]);
unset($zaznamy[1]);
// rozseparuje riadok na predmety
$predmety =  preg_split("/;/", $zaznamy[2]);
// ziska zoznam predmetov z riadku, predmety idu ale az od 4 stlpca
$predmety = array_slice($predmety, 3);
// vyhodime grant total
array_pop($predmety);
// spravne kodovanie aby vedel robit match na predmety
convertEncoding($predmety);
// vyhodime aj predmety aby boli len studenti
unset($zaznamy[2]);

$studenti = array(); // kluc bude AIS ID
foreach ($zaznamy as $zaznam)
{
    $hodnoty = preg_split("/;/", $zaznam);
    convertEncoding($hodnoty);
    // vyberie zapisane predmety
    $zapisanePredmety = array_slice($hodnoty, 3);
    // zaevidujeme studenta
    $studenti[$hodnoty[AIS_ID]] = array(
        "meno"				=> $hodnoty[MENO],
        "priezvisko"		=> $hodnoty[PRIEZVISKO],
        "osobne_cislo"		=> $hodnoty[AIS_ID],
        "rocnik"			=> 1, //tieto dva udaje tu nemame ...
        "studijny_program"	=> 1,
        "fakulta"			=> "FIIT STU",
    );
    foreach ($zapisanePredmety as $key => $zapisanyPredmet)
    {
        // nema na vyber zaznaci si len nazov predmetu
        if ($zapisanyPredmet == "1")
        {
            $studenti[$hodnoty[AIS_ID]]["zapisane"][] = $predmety[$key];
        }
    }
}

//var_dump($studenti);
//rollback nech neblokuje  ine updaty lebo bol v transakcii
//$dbh->TransactionRollback();
//throw new Exception("TEST ONLY");

foreach($studenti as $stud)
{
    $dbh->query(
        "SELECT id FROM student
         WHERE osobne_cislo=$1",
        $stud["osobne_cislo"]
    );
    // student neexxistuje je potrebne ho pridat
    if ($dbh->RowCount() === 0)
    {
        $dbh->query(
            "INSERT INTO student (osobne_cislo, meno, priezvisko, fakulta, id_studijny_program, rocnik) ".
            "VALUES ($1, $2, $3, $4, $5, $6)",
            array($stud["osobne_cislo"], $stud["meno"], $stud["priezvisko"],
            $stud["fakulta"], $stud["studijny_program"], $stud["rocnik"])
        );
        echo "pridavam noveho studenta {$stud["meno"]} {$stud["priezvisko"]}.\n";
        // vyberie este id
        $dbh->query("select currval('student_id_seq') AS id");
    }
    // bud vyberie id existujuceho alebo zo sekvencie ked pridal
    $id = $dbh->fetch_assoc();
    $id = $id["id"];

    foreach ($stud["zapisane"] as $predmet)
    {
        // IDcko predmetu berie podla nazvu + vybera len z daneho semestra predmety ...
        // taktiez poznamky nedavame a nevieme ci je to opakujuci student ...
        // WARNING: ak predmet neexistuje tuna to rupne
        // POZNAMKA: nakolko existuje viacero predmetov toho isteho nazvu
        // tak moze namapovat iba na jeden (nie je presne info kolko studentov akeho odboru
        // + nevie akeho predmetu tak da do jedneho)
        $dbh->query(
            "INSERT INTO zapisany_predmet (id_predmet, id_student, opakuje, poznamka) ".
            "VALUES((SELECT id FROM predmet WHERE nazov LIKE $1 AND id_semester=$2 LIMIT 1), $3, 'f', '')",
            array($predmet, SEMESTER, $id)
        );
    }
}

$dbh->TransactionEnd();
?>
