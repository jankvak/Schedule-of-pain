<h2>Vaše pripomienky k systému</h2>
<p>
<a href='all/suggestion/add'>Pridaj pripomienku</a>
</p>
<table class="sorted-table paged-table filtered {sortlist: [[0,1]], pagesizes:[10,25,50], selpagesize: 0}">
	<thead>
		<tr>
			<th align="center" class="{sorter: 'dates-sk'}">Čas vloženia</th>
			<th align="center">Typ</th>
			<th>Text</th>
			<th align="center">Stav</th>
		</tr>
	</thead>
  <tbody>
    <?php
    foreach($suggestions as $suggestion)
    {
      echo "<tr>";
			echo "<td align=\"center\">" . date("d.m.Y H:i", $suggestion["casova_peciatka"]) . "</td>";
			echo "<td align=\"center\">" . $suggestion["typ"] . "</td>";
			echo "<td>" . nl2br($suggestion["text"]) . "</td>";
			echo "<td align=\"center\">" . $suggestion["nazovStavu"] . "</td>";
		  echo "</tr>";
	 }
   ?>
	</tbody>
</table>

<?php
    $url = urldecode($_GET["filter"]);
    echo "<input type='hidden' id='filtHelp' value='{$url}' ";
?>

<script type="text/javascript">
      $(document).ready(function(){
            $("#filter-box-0").val($("#filtHelp").val());
            $("#filter-box-0").keyup();
      });
</script>