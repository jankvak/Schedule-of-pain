<h1>Ooops!</h1>

<p>Stala sa chyba. Uisťujeme Vás, že študenti, ktorí tento systém vytvorili, dostanú Fx. Študentom, ktorí na tomto systéme pracovali po minulé roky bol spätne odobratý diplom.</p>

<p>Vedúci projektu práve vyrazil na služobnú cestu do Afriky, kde sa bude snažiť vyjednať dobrú cenu za cvičené opice, ktoré chybu opravia.</p>

<p>Dovtedy sa môžete vrátiť na <a href="<?php echo BASE_URL; ?>">úvodnú stránku systému</a> (ak vôbec funguje,darebáci), alebo si môžete <a href="http://www.kongregate.com">zahrať hru.</a></p>

<p><a href="#" onClick="$('#error').show(); return false;">Hovoríte po klingonsky?</a></p>

<div id="error" style="display: none;">

<strong>Exception:</strong> <?php echo $exception->backtrace[0]['class']; ?> <br/>
<strong>Message:</strong> <?php echo $exception->getMessage(); ?> <br/>
<strong>Stack trace:</strong>

<table>
  <?php
    foreach($exception->backtrace as $backtrace) {
  ?>
      <tr>
        <td><?php echo $backtrace['file'] ?></td>
        <td><?php echo $backtrace['line'] ?></td>
        <td><?php echo $backtrace['class'] . "->" . $backtrace['function'] ?></td>
      </tr>
  <?php
    }
  ?>
</table>

<p>Tak vám treba!</p>

</div>
