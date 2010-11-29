<?php 
    $zadane = $nezadane = array();
foreach($requirements as $course) {
    if(is_null($course['skratka']) || is_null($course['pred_hod']) || is_null($course['cvic_hod']))
        $nezadane[] = $course;
    else $zadane[] = $course;
}
?>
<h2>Vyberte predmet, ktorého požiadavky chcete exportovať</h2>
<?php
if (empty($requirements)):
?>
<p class="error">Zatiaľ nemáte pridelené žiadne predmety.</p>
<?php
endif;
if (!empty($nezadane)) {
    ?>
<h5>Predmety bez zadaných požiadaviek</h5>
<table class="inplace">
    <tbody>
            <?php
            foreach($nezadane as $course) {
                ?>
        <tr>
            <td width="35%"><?php echo $course['nazov'] ?></td>
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
            <td width="100%" align="right"><a href="garant/export/createdoc/<?php echo $course['id'] ?>">export</a>
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
