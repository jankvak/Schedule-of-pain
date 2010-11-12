Ste prihlásený ako <?php echo $name ?>
<br/>
Vľavo je ponuka s akciami, ku ktorým máte prístup.
<br/>
<br/>
<?php

if(empty($mail)){
    echo "<div style='font-weight: bold;'>Zatiaľ ste si nenastavili svoj e-mail, <a style='font-size:16px; color:red;' href='all/profile/index'>urobte tak tu</a>.</div>";
}

?>
<hr/>
<br/>
<h5>Prebiehajúce akcie</h5>
<?php
function printEventsTable($events)
{
    echo
    "<table class=\"sorted-table filtered {sortlist: [[1,1]]}\">
    <thead>
        <tr>
            <th align=\"center\">Akcia</th>
            <th align=\"center\" class=\"{sorter: 'dates-sk'}\">Začiatok</th>
            <th align=\"center\" class=\"{sorter: 'dates-sk'}\">Koniec</th>
        </tr>
    </thead>
    <tbody>";
    foreach($events as $actEvent)
    {
        echo "<tr>";
        echo "<td align=\"center\">" . $actEvent["title"] . "</td>";
        echo "<td align=\"center\">" . $actEvent["start"] . "</td>";
        echo "<td align=\"center\">" . $actEvent["end"] . "</td>";
        echo "</tr>";
    }
    echo "
    </tbody>
    </table>";

}

if(empty($actualEvents)) echo "Momentálne neprebieha žiadna akcia.";
else  printEventsTable($actualEvents);

?>
<h5>Plánované akcie</h5>
<?php
if(empty($futureEvents)) echo "Momentálne nie sú naplánované žiadne akcie.";
else printEventsTable($futureEvents);

?>
<br/>
<br/>
<hr/>
<br/>
<h3>
    Postup vloženia požiadaviek
</h3>
<br />
Pre vloženie novej požiadavky je potrebné, aby garant nastavil skratku predmetu,
rozsah hodín pre cvičenia a prednášky, určil vedúceho cvičení a prednášajúceho.
Následne bude možné vkladať požiadavky pre pedagógov, ktorí budú mať priradený predmet.
<br />