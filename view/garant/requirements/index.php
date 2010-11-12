<?php
// podme rozsekat data na dve skupiny
$zadane = $nezadane = array();
foreach($requirements as $course) {
    if(is_null($course['skratka']) || is_null($course['pred_hod']) || is_null($course['cvic_hod']))
        $nezadane[] = $course;
    else $zadane[] = $course;
}
?>
<h2>Vyberte predmet, ktorého požiadavky chcete zadať</h2>
<?php
if (empty($requirements)):
?>
<p class="error">Zatiaľ nemáte pridelené žiadne predmety.</p>
<?php
endif;
if (!empty($nezadane) && $read_only_semester != true) {
    ?>
<h5>Predmety bez zadaných požiadaviek</h5>
<table class="inplace">
    <tbody>
            <?php
            foreach($nezadane as $course) {
                ?>
        <tr>
            <td width="35%"><a href="garant/requirements/edit/<?php echo $course['id'] ?>"><?php echo $course['nazov'] ?></a></td>
        </tr>
            <?php
            }
            ?>
    </tbody>
</table>
<br/><br/>
<?php
}
?>
<?php
if (!empty($zadane)) {
    ?>
<h5>Predmety so zadanými požiadavkami</h5>
<table class="inplace">
    <tbody>
            <?php
            foreach($zadane as $course) {
                ?>
        <tr>
            <td width="30%"><?php echo $course['nazov'] ?></td>
            <td width="100%" align="right"><a href="garant/requirements/edit/<?php echo $course['id'] ?>"><?php
            echo $read_only_semester == true ? "prezerať" : "upraviť"?></a>
            </td>
            <td></td>
        </tr>
            <?php
            }
            ?>
    </tbody>
</table>
<?php
}
?>