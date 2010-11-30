<h2>Kolaborácia</h2>
<h6>Zoznam vašich skupín:</h6><br/>
<table class="sorted-table {sortlist: [[0,1]]}">
    <thead>
        <tr>
            <th>Skupina</th>
            <th width="100">Rola</th>
            <th width="235">Dátum posledného príspevku</th>
            <th width="155">Počet príspevkov</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach($collaborations_summary as $collaboration_summary) {
            echo '<tr>';
            echo '<td><a href="all/collaboration/collaboration/' . $collaboration_summary['id'] . '/">' . $collaboration_summary['code'] . ' ' . $collaboration_summary['name'] . '</td>';
            echo '<td align="center">' . $collaboration_summary['user_role'] . '</td>';
            echo '<td align="center">' . date('d.m.Y H:i', $collaboration_summary['post_last_date']) . '</td>';
            echo '<td align="center">' . $collaboration_summary['post_count'] . '</td>';
            echo '</tr>';
        }
        ?>
    </tbody>
</table>
