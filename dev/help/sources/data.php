<?php
$meno = $_POST['meno'];
$predmet = $_POST['predmet'];
$text =  $_POST['text'];
$priezvisko = $_POST['priezvisko'];
$email = $_POST['email'];

if(isset ($_POST['odoslat'])) {

    /*$kontakt = array("ondrej.buch@gmail.com", "krajov@gmail.com", "matej.krchniak@gmail.com", "peterfoxik@gmail.com",
        "mikuska.p@gmail.com", "doopox@gmail.com", "sstevanak@gmail.com");

    $cislo = rand(0, 6);*/
    $prijemca = "tp0910_tim19+hotline@googlegroups.com";
    //$kontakt[$cislo];

    if ($meno && $email && $predmet && $text) {
        mail("$prijemca", "$predmet", "$meno $priezvisko Vám posiela tento text $text","from: $email");
        echo "Váš <b>email bol</b> úspešne <b>odoslaný na adresu $prijemca</b>!";

    }
    elseif (($meno && $email && $predmet && $text) == false) {

        echo "Váš <b>email nebol odoslaný</b>. <b>Nezadali ste všetky</b> povinné
<b>údaje</b>.";

    }
}
?>
