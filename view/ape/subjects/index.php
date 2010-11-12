<script type="text/javascript" src="js/set_block_flag_subject.js"></script>

<h2>Evidencia predmetov</h2>
<p><a href="ape/subjects/add">Pridaj nový predmet</a></p>
<p><a href="ape/subjects/getPrevPeriodSubjects">Prebrať predmety z minuloročného semestra</a></p>
<table class="sorted-table filtered {sortlist: [[0,0]], pagesizes:[15,30,60], selpagesize: 0}">
	<thead>
		<tr>
			<th>Názov</th>
			<th>Kód</th>
			<th>Semester</th>
			<th>Študijný program</th>
			<th>Spôsob ukončenia</th>
            <th><span class="tooltip" title="Počet študentov, ktorí majú zapísaný daný predmet">Štud.</span></th>
			<th class="{sorter: false}">Preberanie požiadaviek</th>
			<th class="{sorter: false}">Akcie</th>
		</tr>
	</thead>
	<tbody>
	<?php
		foreach ($predmety as $pr){
			echo "<tr id={$pr['id']}><td>{$pr['nazov']}</td><td>{$pr['kod']}</td><td>{$pr['semester']}</td>";
			foreach ($programy as $prog){
				if ($prog['id']==$pr['studijny_program']) echo "<td>{$prog['nazov']}</td>";
			}
			foreach ($ukoncenia as $uk){
				if ($uk['id']==$pr['sposob_ukoncenia']) echo "<td>{$uk['nazov']}</td>";
			}
            echo "<td>{$pr["studentov"]}</td>\n";
			$checked ="";
			if ($pr['blokovat_preberanie']) $checked= "checked";
			echo "<td><input type='checkbox' class='blocking' value={$pr['id']} {$checked} > blokovať</td>";
			echo "<td><a href='ape/subjects/edit/{$pr['id']}'>Upraviť</a><br/><a href='ape/subjects/delete/{$pr['id']}'>Vymazať</a></td></tr>";
		}
        if (empty($predmety))
        {
            echo "<tr><td colspan=\"7\">Zatiaľ nie sú evidované žiadne predmety.</td></tr>";
        }
	?>
	</tbody>
</table>

<!--
Formular na zadanie komentaru pre blokovanie preberania poziadaviek
Zobrazi sa po oznaceni checkboxu pri konrektnom predmete
-->
<div id="comment">
        <div>
            <div class="row">
            <b id="commentTitle"><!-- text sa doplni javascriptom --></b>
            </div>
            <div class="row">
                Sem zadajte dôvod zablokovania (Nepovinné)
            </div>
            <div class="row">
                <textarea rows="3" style="height:52px;" cols="70" id="blockComment"></textarea><br />
                <input type="hidden" id="subjectId" value="">
                <input type="submit" id="saveComment" value="Odoslať">
                <input type="submit" id="cancel" value="Zrušiť">
            </div>
        </div>
</div>