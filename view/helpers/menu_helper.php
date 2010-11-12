<?php

// TODO: co takto odseparovat kod do samostatneho suboru a includnut ako sablonu ?
class MenuHelper extends Helper {

	/**
	 * Vrati HTML kod pre vyber semestra, pricom vybrany bude aktivny semester
	 * @param $semestre - zoznam semestrov
	 * @param $selectedSem - aktivny semester
	 * @param $url - url aktualne navstevovanej stranky
	 * @return String
	 */
	function renderSemester($semestre, $selectedSem, $url)
	{
		// iba ak je prihlaseny generuje formular
		$out =
"<form action=\"user/change_sem\" method=\"post\">
<input type=\"hidden\" name=\"redirect\" value=\"{$url}\" />
<label for=\"semester\">Obdobie: </label><select id=\"semester\" name=\"semester\">
";
		foreach ($semestre as $semester)
		{
			switch ($semester["semester"])
			{
				case 1: $skratka = "ZS"; break;
				case 2: $skratka = "LS"; break;
				default: $skratka = "ERR ?";
			}
			$id = $semester["id"];
			$rok[1] = $semester["rok"];
			$rok[2] = $rok[1]+1;
			$nazov = "{$skratka} - {$rok[1]}/{$rok[2]}";
			$sel = ($selectedSem == $id) ? " selected=\"selected\"" : "";
			$out .= "<option value=\"{$id}\"{$sel}>{$nazov}</option>";
		}
		$out .=
"</select>
<input type=\"submit\" name=\"change_sem\" value=\"Zmeň\" />
</form>";
		return $out;
	}

	/**
	 * Vrati HTML kod menu
	 * @param $menuItems - polozky menu daneho usera
	 * @param $adminActing - admin niekoho zastupuje ?
	 * @return String
	 */
	function render($menuItems, $adminActing) {

		$lastGroup = "";
		$out = '';

		if(!empty($menuItems)) {
			foreach($menuItems as $item) {
				if($lastGroup != $item["nazov"]) {
					$out .= "<li><h1 class='menu_title'>{$item["nazov"]}</h1></li>\n";
					$lastGroup = $item["nazov"];
				}

				$out .= "<li><a href='{$item["href"]}'>{$item["name"]}</a></li>\n";
			}
		}

		$out .= "<li><h1 class='menu_title'>System</h1></li>";
		if ($adminActing === true) {
			$out .= "<li><a href='auth/restoreAdmin'>Obnoviť práva</a></li>\n";
		}
		$out .= "<li><a href='auth/logout'>Logout</a></li>";

		return $out;
	}
}

?>
