<script type="text/javascript" src="js/periods-datepicker.js"></script>
<div id="dp"></div>
<form action="ape/periods/save" method="post">
    <input type="hidden" name="id" value="<?php if (isset($semester)) echo $semester["id"]; ?>" />
	<p>
		<label for="rok">Akademický rok</label>
		<input type="text" id="rok" name="rok" maxlength="4" size="4" value="<?php if (isset($semester)) echo $semester["rok"]; else echo date("Y");?>" />	
	</p>
	<p>
		<label for="semester">Semester</label>
		<select id="semester" name="semester">
			<option value="1" <?php if (isset($semester) && $semester["semester"] == 1) echo "selected=\"selected\""?>>ZS</option>
			<option value="2" <?php if (isset($semester) && $semester["semester"] == 2) echo "selected=\"selected\""?>>LS</option>
		</select>
	</p>
	<p>
		<label for="zac_uc">Začiatok semestra</label>
		<input type="text" id="zac_uc" name="zac_uc" maxlength="10" size="10" value="<?php echoParam($semester["zac_uc"]);?>"/>
	</p>
	<p>
		<label for="kon_uc">Koniec semestra</label>
		<input type="text" id="kon_uc" name="kon_uc" maxlength="10" size="10" value="<?php echoParam($semester["kon_uc"]);?>"/>
	</p>
	<p>
		<label for="zac_skus">Začiatok skúškového</label>
		<input type="text" id="zac_skus" name="zac_skus" maxlength="10" size="10"  value="<?php echoParam($semester["zac_skus"]);?>"/>
	</p>
	<p>
		<label for="zac_opr">Začiatok opravných skúšok</label>
		<input type="text" id="zac_opr" name="zac_opr" maxlength="10" size="10" value="<?php echoParam($semester["zac_opr"]);?>"/>
	</p>
	<p>
		<label for="kon_skus">Koniec skúškového</label>
		<input type="text" id="kon_skus" name="kon_skus" maxlength="10" size="10" value="<?php echoParam($semester["kon_skus"]);?>"/>
	</p>
	<p>
	   <?php 
	   if (empty($semester["id"])) echo "<input type=\"submit\" value=\"Pridaj\" />";
	   else echo "<input type=\"submit\" value=\"Upraviť\" />"; 
	   ?>		
	</p>
</form>
<p>
<a href="ape/periods/index">Naspäť</a>
</p>
