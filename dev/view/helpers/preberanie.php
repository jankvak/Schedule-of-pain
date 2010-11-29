<?php
/* form zobrazime len ked existuju nejake predmety z minuleho roku */
function zobrazMinulorocnePredmety($minule, $subject, $url, $blokovanie_preberania)
{
		if ($blokovanie_preberania['blokovat_preberanie']) {
			echo "<div class='row' style='color:red'><b>Nedá sa prebrať požiadavka z minulého roku.</div>";
			echo "<div class='row'>Dôvod: </b>";
			if (empty($blokovanie_preberania['dovod_blokovania'])) {
				//echo "<div class='row'>";
				echo "Nezadaný.<br/><br/>";
				echo "</div>";
			}
			else {
				echo "</div><div class='row'>";
				echo "<textarea rows='3' style='height:52px;' cols='70' disabled>{$blokovanie_preberania['dovod_blokovania']}</textarea><br />";
				echo "</div>";
			}
			return;
		}
?>
<div class="row">
<form id="preberanie" style="padding-bottom: 30px; margin-left: 10px; border-bottom: 0px;" method="post" action="<?php echo $url; ?>">
  <label for="minule">Vyplniť podľa minuloročného predmetu:</label>
  <select id="minule" name="id_minuly" style="width: 385px;">    
    <?php
      if (count($minule['ZS'])==0) 
        echo '<optgroup label="Zimný semester - žiadne predmety">';
      else
        echo '<optgroup label="Zimný semester">';
      foreach ($minule['ZS'] as $minuly)
      {
        echo "<option value=\"{$minuly['id']}\"";
        if ($minuly['nazov']==$subject['nazov'])
          echo ' selected="selected"';
        echo ">{$minuly['nazov']}</option>";
      }
      echo '</optgroup>';

      if (count($minule['LS'])==0) 
        echo '<optgroup label="Letný semester - žiadne predmety">';
      else
        echo '<optgroup label="Letný semester">';
      foreach ($minule['LS'] as $minuly)
      {
        echo "<option value=\"{$minuly['id']}\"";
        if ($minuly['nazov']==$subject['nazov'])
          echo ' selected="selected"';
        echo ">{$minuly['nazov']}</option>";
      }
      echo '</optgroup>';
    ?>
  </select>
  <input type="submit" value="Vyplň"/>
</form>
</div>
<?php
}
?>